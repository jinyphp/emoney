<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Emoney\Models\UserPoint;
use Jiny\Emoney\Models\UserPointLog;
use Jiny\Emoney\Models\UserPointExpiry;

/**
 * 관리자 - 포인트 통계 관리 컨트롤러
 */
class StatsController extends Controller
{
    /**
     * 포인트 통계 정보 표시
     */
    public function __invoke(Request $request)
    {
        $period = $request->get('period', '1month');

        // 기간별 데이터 설정
        $endDate = now();
        switch ($period) {
            case '1week':
                $startDate = now()->subWeek();
                $dateFormat = '%Y-%m-%d';
                $groupBy = 'DATE(created_at)';
                break;
            case '1month':
                $startDate = now()->subMonth();
                $dateFormat = '%Y-%m-%d';
                $groupBy = 'DATE(created_at)';
                break;
            case '3month':
                $startDate = now()->subMonths(3);
                $dateFormat = '%Y-%m-%d';
                $groupBy = 'DATE(created_at)';
                break;
            case '6month':
                $startDate = now()->subMonths(6);
                $dateFormat = '%Y-%m';
                $groupBy = 'DATE_FORMAT(created_at, "%Y-%m")';
                break;
            case '1year':
                $startDate = now()->subYear();
                $dateFormat = '%Y-%m';
                $groupBy = 'DATE_FORMAT(created_at, "%Y-%m")';
                break;
            default:
                $startDate = now()->subMonth();
                $dateFormat = '%Y-%m-%d';
                $groupBy = 'DATE(created_at)';
        }

        // 전체 통계
        $overallStats = [
            'total_users' => UserPoint::count(),
            'total_balance' => UserPoint::sum('balance'),
            'total_earned' => UserPoint::sum('total_earned'),
            'total_used' => UserPoint::sum('total_used'),
            'total_expired' => UserPoint::sum('total_expired'),
            'avg_balance' => UserPoint::avg('balance'),
            'users_with_balance' => UserPoint::where('balance', '>', 0)->count(),
            'max_balance' => UserPoint::max('balance'),
            'pending_expiry_amount' => UserPointExpiry::where('expired', false)->sum('amount'),
        ];

        // 기간별 거래 통계
        $periodStats = UserPointLog::select(
            DB::raw("{$groupBy} as period"),
            DB::raw('count(*) as total_transactions'),
            DB::raw('SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_earned'),
            DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as total_used'),
            DB::raw('SUM(CASE WHEN transaction_type = "earn" THEN 1 ELSE 0 END) as earn_count'),
            DB::raw('SUM(CASE WHEN transaction_type = "use" THEN 1 ELSE 0 END) as use_count'),
            DB::raw('SUM(CASE WHEN transaction_type = "refund" THEN 1 ELSE 0 END) as refund_count'),
            DB::raw('SUM(CASE WHEN transaction_type = "expire" THEN 1 ELSE 0 END) as expire_count'),
            DB::raw('SUM(CASE WHEN transaction_type = "admin" THEN 1 ELSE 0 END) as admin_count')
        )
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy('period')
        ->orderBy('period')
        ->get();

        // 거래 유형별 통계
        $transactionTypeStats = UserPointLog::select(
            'transaction_type',
            DB::raw('count(*) as count'),
            DB::raw('SUM(ABS(amount)) as total_amount'),
            DB::raw('AVG(ABS(amount)) as avg_amount')
        )
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy('transaction_type')
        ->get();

        // 참조 유형별 통계
        $referenceTypeStats = UserPointLog::select(
            'reference_type',
            DB::raw('count(*) as count'),
            DB::raw('SUM(ABS(amount)) as total_amount')
        )
        ->whereNotNull('reference_type')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy('reference_type')
        ->orderBy('total_amount', 'desc')
        ->limit(10)
        ->get();

        // 상위 포인트 보유자
        $topHolders = UserPoint::with('user')
            ->orderBy('balance', 'desc')
            ->limit(10)
            ->get();

        // 최근 대량 거래
        $recentLargeTransactions = UserPointLog::with(['user', 'admin'])
            ->where('created_at', '>=', now()->subDays(7))
            ->where(DB::raw('ABS(amount)'), '>=', 1000) // 1000포인트 이상
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // 만료 예정 통계
        $expiryStats = [
            'expiring_today' => UserPointExpiry::where('expired', false)
                ->whereDate('expires_at', today())
                ->sum('amount'),
            'expiring_this_week' => UserPointExpiry::where('expired', false)
                ->whereBetween('expires_at', [now(), now()->addWeek()])
                ->sum('amount'),
            'expiring_this_month' => UserPointExpiry::where('expired', false)
                ->whereBetween('expires_at', [now(), now()->addMonth()])
                ->sum('amount'),
            'total_pending' => UserPointExpiry::where('expired', false)
                ->sum('amount'),
        ];

        // 포인트 분포 (잔액 구간별)
        $balanceDistribution = UserPoint::select(
            DB::raw('
                CASE
                    WHEN balance = 0 THEN "0"
                    WHEN balance <= 100 THEN "1-100"
                    WHEN balance <= 500 THEN "101-500"
                    WHEN balance <= 1000 THEN "501-1000"
                    WHEN balance <= 5000 THEN "1001-5000"
                    WHEN balance <= 10000 THEN "5001-10000"
                    ELSE "10000+"
                END as range
            '),
            DB::raw('count(*) as count'),
            DB::raw('sum(balance) as total_amount')
        )
        ->groupBy('range')
        ->orderBy(DB::raw('
            CASE
                WHEN balance = 0 THEN 1
                WHEN balance <= 100 THEN 2
                WHEN balance <= 500 THEN 3
                WHEN balance <= 1000 THEN 4
                WHEN balance <= 5000 THEN 5
                WHEN balance <= 10000 THEN 6
                ELSE 7
            END
        '))
        ->get();

        return view('jiny-emoney::point.stats', [
            'period' => $period,
            'overall_stats' => $overallStats,
            'period_stats' => $periodStats,
            'transaction_type_stats' => $transactionTypeStats,
            'reference_type_stats' => $referenceTypeStats,
            'top_holders' => $topHolders,
            'recent_large_transactions' => $recentLargeTransactions,
            'expiry_stats' => $expiryStats,
            'balance_distribution' => $balanceDistribution,
            'request' => $request,
        ]);
    }
}