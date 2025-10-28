<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 관리자 - 포인트 관리 메인 컨트롤러
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