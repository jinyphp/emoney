<?php

namespace Jiny\Emoney\Http\Controllers\Emoney\Withdraw;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 이머니 출금 내역
 *
 * [메소드 호출 관계 트리]
 * HistoryController
 * └── __invoke(Request $request)
 *     ├── getAuthenticatedUser($request) - JWT 다중 인증 방식으로 사용자 확인
 *     ├── 필터링 파라미터 수집
 *     │   ├── status - 출금 상태 필터
 *     │   ├── date_from, date_to - 날짜 범위 필터
 *     │   └── per_page - 페이지당 표시 개수
 *     ├── DB::table('user_emoney_withdrawals') - 출금 내역 쿼리 구성
 *     │   ├── ->where('user_uuid', $userUuid) - 해당 사용자만
 *     │   ├── ->where('status', $status) - 상태 필터 적용
 *     │   ├── ->whereDate('created_at', '>=', $dateFrom) - 시작일 필터
 *     │   ├── ->whereDate('created_at', '<=', $dateTo) - 종료일 필터
 *     │   ├── ->count() - 총 개수 조회
 *     │   ├── ->orderBy('created_at', 'desc') - 최신순 정렬
 *     │   ├── ->offset(), ->limit() - 페이지네이션 처리
 *     │   └── ->get() - 데이터 조회
 *     ├── 페이지네이션 정보 계산
 *     │   ├── currentPage, totalPages 계산
 *     │   ├── has_prev, has_next 계산
 *     │   └── prev_page, next_page 계산
 *     ├── 통계 정보 조회
 *     │   ├── total_requests - 총 출금 신청 건수
 *     │   ├── pending/approved/rejected_requests - 상태별 건수
 *     │   ├── total_withdrawn - 총 출금 금액 (승인된 것만)
 *     │   └── total_fees - 총 수수료 금액
 *     ├── DB::table('user_emoney')->where('user_uuid', $userUuid)->first() - 현재 이머니 정보
 *     └── view('jiny-emoney::home.withdraw.history', $data) - 출금 내역 뷰 렌더링
 *
 * [컨트롤러 역할]
 * - 사용자의 이머니 출금 내역 조회 및 표시
 * - 출금 상태별 필터링 (pending, approved, rejected)
 * - 날짜 범위별 필터링
 * - 페이지네이션 처리
 * - 출금 관련 통계 정보 제공
 * - 현재 이머니 잔액 정보 표시
 *
 * [필터링 기능]
 * - 상태 필터: pending(대기), approved(승인), rejected(거부)
 * - 날짜 범위 필터: 시작일부터 종료일까지
 * - 페이지당 표시 개수: 기본 10개
 *
 * [통계 정보]
 * - total_requests: 총 출금 신청 건수
 * - pending_requests: 승인 대기 중인 건수
 * - approved_requests: 승인된 건수
 * - rejected_requests: 거부된 건수
 * - total_withdrawn: 총 출금 금액 (승인된 것만)
 * - total_fees: 총 수수료 금액
 *
 * [페이지네이션]
 * - 수동으로 페이지네이션 구현
 * - current_page, total_pages, total_count
 * - has_prev, has_next - 이전/다음 페이지 존재 여부
 * - prev_page, next_page - 이전/다음 페이지 번호
 *
 * [출금 상태]
 * - pending: 승인 대기 중
 * - approved: 승인됨 (실제 출금 처리됨)
 * - rejected: 거부됨
 * - processing: 처리 중 (옵션)
 * - completed: 완료됨 (옵션)
 *
 * [라우트 연결]
 * Route: GET /emoney/withdraw/history
 * Name: home.emoney.withdraw.history
 *
 * [관련 컨트롤러]
 * - IndexController: 출금 페이지 표시
 * - StoreController: 출금 신청 처리
 *
 * [뷰 데이터]
 * - user: 사용자 정보
 * - emoney: 현재 이머니 잔액 정보
 * - withdrawals: 출금 내역 목록
 * - statistics: 출금 관련 통계
 * - filters: 현재 적용된 필터 정보
 * - pagination: 페이지네이션 정보
 */
class HistoryController extends Controller
{
    use JWTAuthTrait;

    public function __invoke(Request $request)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $userUuid = $user->uuid;

        // 필터링 파라미터
        $status = $request->get('status', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');
        $perPage = $request->get('per_page', 10);

        // 출금 내역 조회
        $query = DB::table('user_emoney_withdrawals')
            ->where('user_uuid', $userUuid);

        // 상태 필터
        if ($status) {
            $query->where('status', $status);
        }

        // 날짜 필터
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // 페이지네이션을 위한 총 개수 및 데이터 조회
        $totalCount = $query->count();
        $withdrawals = $query->orderBy('created_at', 'desc')
            ->offset(($request->get('page', 1) - 1) * $perPage)
            ->limit($perPage)
            ->get();

        // 페이지네이션 정보 계산
        $currentPage = $request->get('page', 1);
        $totalPages = ceil($totalCount / $perPage);

        // 통계 정보 조회
        $stats = [
            'total_requests' => DB::table('user_emoney_withdrawals')->where('user_uuid', $userUuid)->count(),
            'pending_requests' => DB::table('user_emoney_withdrawals')->where('user_uuid', $userUuid)->where('status', 'pending')->count(),
            'approved_requests' => DB::table('user_emoney_withdrawals')->where('user_uuid', $userUuid)->where('status', 'approved')->count(),
            'rejected_requests' => DB::table('user_emoney_withdrawals')->where('user_uuid', $userUuid)->where('status', 'rejected')->count(),
            'total_withdrawn' => DB::table('user_emoney_withdrawals')->where('user_uuid', $userUuid)->where('status', 'approved')->sum('amount') ?? 0,
            'total_fees' => DB::table('user_emoney_withdrawals')->where('user_uuid', $userUuid)->where('status', 'approved')->sum('fee') ?? 0,
        ];

        // 사용자 이머니 정보
        $emoney = DB::table('user_emoney')->where('user_uuid', $userUuid)->first();

        return view('jiny-emoney::home.withdraw.history', [
            'user' => $user,
            'emoney' => $emoney,
            'withdrawals' => $withdrawals,
            'statistics' => $stats,
            'filters' => [
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage
            ],
            'pagination' => [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'total_count' => $totalCount,
                'per_page' => $perPage,
                'has_prev' => $currentPage > 1,
                'has_next' => $currentPage < $totalPages,
                'prev_page' => max(1, $currentPage - 1),
                'next_page' => min($totalPages, $currentPage + 1)
            ]
        ]);
    }
}