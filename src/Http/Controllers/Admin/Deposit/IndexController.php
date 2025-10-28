<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Deposit;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Facades\Shard;

/**
 * 관리자 - 충전 신청 목록 조회
 *
 * [메소드 호출 관계 트리]
 * IndexController
 * └── __invoke(Request $request)
 *     ├── 사용자 검색 처리
 *     │   ├── filter_var($search, FILTER_VALIDATE_EMAIL) - 이메일 형식 확인
 *     │   ├── Shard::getUserByEmail($search) - 이메일로 사용자 조회 (샤딩 시스템)
 *     │   └── DB::table('users')->where('name', 'like', "%{$search}%")->pluck('uuid') - 이름으로 사용자 검색
 *     ├── 충전 신청 쿼리 구성
 *     │   ├── DB::table('user_emoney_deposits')->select(['*']) - 충전 신청 테이블 조회
 *     │   ├── $query->where() - 다양한 필터 조건 적용
 *     │   │   ├── 검색 필터: depositor_name, reference_number, user_uuid
 *     │   │   ├── 상태 필터: status
 *     │   │   ├── 충전 방법 필터: method
 *     │   │   ├── 은행 필터: bank_name
 *     │   │   ├── 날짜 범위 필터: created_at
 *     │   │   └── 금액 범위 필터: amount
 *     │   ├── $query->orderBy($sortBy, $sortOrder) - 정렬 적용
 *     │   └── $query->paginate($perPage)->withQueryString() - 페이지네이션
 *     ├── 사용자 정보 조회 및 매핑
 *     │   ├── $deposits->pluck('user_uuid')->unique()->filter()->toArray() - 고유 UUID 수집
 *     │   ├── foreach 루프로 Shard::getUserByUuid($uuid) - 각 UUID별 사용자 정보 조회
 *     │   └── $deposits->getCollection()->transform() - 사용자 정보를 충전 데이터에 매핑
 *     ├── 통계 정보 계산
 *     │   ├── DB::table()->count() - 각 상태별 충전 건수
 *     │   ├── DB::table()->sum('amount') - 승인된 충전 총액
 *     │   └── DB::table()->whereDate('created_at', today()) - 오늘 충전 통계
 *     └── view('jiny-emoney::admin.deposit.index', $data) - 뷰 렌더링
 *
 * [컨트롤러 역할]
 * - 관리자용 충전 신청 목록 페이지 제공
 * - 샤딩된 사용자 시스템과 연동하여 사용자 정보 조회
 * - 다양한 필터링 옵션 (검색, 상태, 방법, 은행, 날짜, 금액)
 * - 충전 신청 통계 정보 제공
 * - 페이지네이션 및 정렬 기능 지원
 *
 * [샤딩 시스템 연동]
 * - Shard::getUserByEmail() - 이메일로 샤딩된 사용자 조회
 * - Shard::getUserByUuid() - UUID로 샤딩된 사용자 조회
 * - 사용자 정보와 충전 신청 데이터를 별도 조회 후 매핑
 *
 * [필터링 기능]
 * - 사용자 검색: 이메일, 이름, 입금자명, 참조번호
 * - 상태 필터: pending, approved, rejected
 * - 충전 방법 필터: 각종 충전 방법
 * - 은행 필터: 은행명 부분 일치
 * - 날짜 범위: 시작일, 종료일
 * - 금액 범위: 최소금액, 최대금액
 *
 * [통계 정보]
 * - 총 충전 신청 건수, 상태별 건수
 * - 승인된 총 충전 금액
 * - 오늘의 충전 신청 건수 및 금액
 *
 * [라우트 연결]
 * Route: GET /admin/auth/emoney/deposits
 * Name: admin.auth.emoney.deposits.index
 *
 * [관련 컨트롤러]
 * - ShowController: 상세보기
 * - ApproveController: 승인 처리
 * - RejectController: 거부 처리
 * - DeleteController: 삭제 처리
 *
 * [보안 고려사항]
 * - 안전한 컬럼명 확인으로 SQL 인젝션 방지
 * - 샤딩 시스템을 통한 안전한 사용자 정보 조회
 */
class IndexController extends Controller
{
    /**
     * 충전 신청 목록 표시
     */
    public function __invoke(Request $request)
    {
        // 사용자 검색을 위한 UUID 수집
        $searchUserUuids = [];
        if ($request->filled('search')) {
            $search = $request->search;

            // 이메일로 사용자 검색 (Shard 파사드 사용)
            if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
                $user = Shard::getUserByEmail($search);
                if ($user && isset($user->uuid)) {
                    $searchUserUuids[] = $user->uuid;
                }
            } else {
                // 이메일이 아닌 경우 기본 테이블에서 이름으로 검색
                try {
                    $users = DB::table('users')
                        ->where('name', 'like', "%{$search}%")
                        ->pluck('uuid');
                    $searchUserUuids = $users->toArray();
                } catch (\Exception $e) {
                    // 테이블 오류시 빈 배열 유지
                    $searchUserUuids = [];
                }
            }
        }

        // 충전 신청 쿼리 구성 (사용자 테이블 조인 제거)
        $query = DB::table('user_emoney_deposits')
            ->select(['*']);

        // 검색 필터 (사용자 UUID 또는 입금자명, 참조번호로 검색)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $searchUserUuids) {
                // 입금자명이나 참조번호로 검색
                $q->where('depositor_name', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");

                // 검색된 사용자 UUID가 있으면 추가
                if (!empty($searchUserUuids)) {
                    $q->orWhereIn('user_uuid', $searchUserUuids);
                }
            });
        }

        // 상태 필터
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 충전 방법 필터
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // 은행 필터
        if ($request->filled('bank')) {
            $query->where('bank_name', 'like', "%{$request->bank}%");
        }

        // 날짜 범위 필터
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // 금액 범위 필터
        if ($request->filled('amount_from')) {
            $query->where('amount', '>=', $request->amount_from);
        }
        if ($request->filled('amount_to')) {
            $query->where('amount', '<=', $request->amount_to);
        }

        // 정렬
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // 안전한 컬럼명 확인
        $allowedSortColumns = ['created_at', 'amount', 'status', 'checked_at'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 페이지네이션
        $perPage = $request->get('per_page', 20);
        $deposits = $query->paginate($perPage)->withQueryString();

        // 충전 신청에서 사용자 UUID 수집 및 사용자 정보 조회
        $userUuids = $deposits->pluck('user_uuid')->unique()->filter()->toArray();
        $users = [];

        // 각 UUID에 대해 개별적으로 사용자 정보 조회
        foreach ($userUuids as $uuid) {
            $user = Shard::getUserByUuid($uuid);
            if ($user) {
                $users[$uuid] = $user;
            }
        }

        // 충전 신청에 사용자 정보 추가
        $deposits->getCollection()->transform(function ($deposit) use ($users) {
            $user = $users[$deposit->user_uuid] ?? null;
            $deposit->user_name = $user->name ?? 'N/A';
            $deposit->user_email = $user->email ?? 'N/A';
            return $deposit;
        });

        // 통계 정보
        $statistics = [
            'total_deposits' => DB::table('user_emoney_deposits')->count(),
            'pending_deposits' => DB::table('user_emoney_deposits')->where('status', 'pending')->count(),
            'approved_deposits' => DB::table('user_emoney_deposits')->where('status', 'approved')->count(),
            'rejected_deposits' => DB::table('user_emoney_deposits')->where('status', 'rejected')->count(),
            'total_amount' => DB::table('user_emoney_deposits')->where('status', 'approved')->sum('amount'),
            'today_deposits' => DB::table('user_emoney_deposits')->whereDate('created_at', today())->count(),
            'today_amount' => DB::table('user_emoney_deposits')
                ->whereDate('created_at', today())
                ->where('status', 'approved')
                ->sum('amount'),
        ];

        return view('jiny-emoney::admin.deposit.index', [
            'deposits' => $deposits,
            'statistics' => $statistics,
            'request' => $request,
        ]);
    }
}