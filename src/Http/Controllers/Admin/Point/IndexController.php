<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 관리자 - 포인트 관리 메인 컨트롤러
 *
 * [메소드 호출 관계 트리]
 * IndexController
 * └── __invoke(Request $request)
 *     ├── 포인트 통계 초기화 (기본값 설정)
 *     ├── 사용자 포인트 쿼리 구성
 *     │   ├── DB::table('user_point')->join('users') - 사용자 테이블과 조인
 *     │   ├── 검색 필터 적용 (사용자명, 이메일)
 *     │   ├── 잔액 범위 필터 적용 (최소/최대 잔액)
 *     │   └── $query->paginate($perPage) - 페이지네이션 적용
 *     ├── 전체 통계 계산
 *     │   ├── DB::table('user_point')->count() - 총 사용자 수
 *     │   ├── DB::table('user_point')->sum('balance') - 총 포인트 잔액
 *     │   ├── DB::table('user_point')->sum('total_earned/used/expired') - 각종 포인트 합계
 *     │   ├── DB::table('user_point_log')->count() - 최근 거래 건수
 *     │   └── DB::table('user_point_expiry')->sum('amount') - 만료 예정 포인트
 *     ├── 관리자 조정 내역 조회
 *     │   └── DB::table('user_point_log')->leftJoin('users') - 최근 관리자 조정 10건
 *     ├── 사용자 활동 내역 조회
 *     │   └── DB::table('user_point_log')->leftJoin('users') - 최근 사용자 활동 10건
 *     └── view('jiny-emoney::admin.point.index', $data) - 뷰 렌더링
 *
 * [컨트롤러 역할]
 * - 관리자용 포인트 시스템 대시보드
 * - 전체 포인트 통계 및 현황 제공
 * - 사용자별 포인트 잔액 및 활동 내역 표시
 * - 포인트 검색 및 필터링 기능
 * - 관리자 조정 내역 및 사용자 활동 모니터링
 *
 * [통계 정보]
 * - 총 사용자 수, 총 포인트 잔액
 * - 총 적립/사용/만료 포인트
 * - 활성 사용자 수, 최근 거래 건수
 * - 만료 예정 포인트, 평균 잔액
 * - 상위 포인트 보유자 TOP 5
 *
 * [실시간 모니터링]
 * - 최근 관리자 조정 내역 (10건)
 * - 최근 사용자 포인트 활동 (10건)
 * - 오늘의 포인트 활동 통계
 *
 * [라우트 연결]
 * Route: GET /admin/auth/point
 * Name: admin.auth.point.index
 *
 * [예외 처리]
 * - 테이블 존재하지 않을 경우 경고 로그 기록
 * - 오류 발생 시에도 빈 데이터로 뷰 렌더링
 */
class IndexController extends Controller
{
    /**
     * 관리자 포인트 관리 메인 페이지
     */
    public function __invoke(Request $request)
    {
        // 전체 포인트 통계
        $userPoints = collect();
        $statistics = [
            'total_users' => 0,
            'total_balance' => 0,
            'total_earned' => 0,
            'total_used' => 0,
            'total_expired' => 0,
            'active_users' => 0,
            'recent_transactions' => 0,
            'expiring_soon' => 0
        ];

        try {
            // 필터링 조건 설정
            $query = DB::table('user_point')
                ->join('users', 'user_point.user_id', '=', 'users.id')
                ->select(
                    'user_point.*',
                    'users.name as user_name',
                    'users.email as user_email'
                );

            // 검색 필터
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                      ->orWhere('users.email', 'like', "%{$search}%");
                });
            }

            // 잔액 필터
            if ($request->filled('balance_min')) {
                $query->where('user_point.balance', '>=', $request->balance_min);
            }

            if ($request->filled('balance_max')) {
                $query->where('user_point.balance', '<=', $request->balance_max);
            }

            // 페이지당 항목 수
            $perPage = $request->get('per_page', 20);

            $userPoints = $query->orderBy('user_point.balance', 'desc')->paginate($perPage);

            // 전체 통계 계산
            $statistics = [
                'total_users' => DB::table('user_point')->count(),
                'total_balance' => DB::table('user_point')->sum('balance'),
                'total_earned' => DB::table('user_point')->sum('total_earned'),
                'total_used' => DB::table('user_point')->sum('total_used'),
                'total_expired' => DB::table('user_point')->sum('total_expired'),
                'active_users' => DB::table('user_point')->where('balance', '>', 0)->count(),
                'recent_transactions' => DB::table('user_point_log')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
                'expiring_soon' => DB::table('user_point_expiry')
                    ->where('expired', false)
                    ->where('expires_at', '<=', now()->addDays(30))
                    ->sum('amount'),
                'avg_balance' => DB::table('user_point')->avg('balance') ?? 0,
                'users_with_balance' => DB::table('user_point')->where('balance', '>', 0)->count(),
                'top_holders' => DB::table('user_point')
                    ->join('users', 'user_point.user_id', '=', 'users.id')
                    ->select('user_point.*', 'users.name as user_name', 'users.email as user_email')
                    ->orderBy('user_point.balance', 'desc')
                    ->limit(5)
                    ->get()
            ];

            // 최근 관리자 조정 내역 (최근 10건)
            $recent_admin_adjustments = DB::table('user_point_log as upl')
                ->leftJoin('users as admin', 'upl.admin_id', '=', 'admin.id')
                ->leftJoin('users as user', 'upl.user_id', '=', 'user.id')
                ->where('upl.transaction_type', 'admin')
                ->orderBy('upl.created_at', 'desc')
                ->limit(10)
                ->select([
                    'upl.amount',
                    'upl.reason',
                    'upl.created_at',
                    'admin.name as admin_name',
                    'user.name as user_name'
                ])
                ->get();

            // 최근 회원 포인트 활동 (최근 10건)
            $recent_user_activities = DB::table('user_point_log as upl')
                ->leftJoin('users as user', 'upl.user_id', '=', 'user.id')
                ->where('upl.transaction_type', '!=', 'admin')
                ->orderBy('upl.created_at', 'desc')
                ->limit(10)
                ->select([
                    'upl.amount',
                    'upl.transaction_type',
                    'upl.reason',
                    'upl.created_at',
                    'user.name as user_name'
                ])
                ->get();

        } catch (\Exception $e) {
            // 테이블이 없는 경우 무시
            \Log::warning('Admin point controller query failed', [
                'error' => $e->getMessage()
            ]);
        }

        return view('jiny-emoney::admin.point.index', [
            'userPoints' => $userPoints,
            'statistics' => $statistics,
            'recent_admin_adjustments' => $recent_admin_adjustments ?? [],
            'recent_user_activities' => $recent_user_activities ?? [],
            'request' => $request,
        ]);
    }
}