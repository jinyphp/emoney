<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Emoney\Models\UserPointLog;

/**
 * 관리자 - 포인트 거래 로그 관리 컨트롤러
 */
class LogController extends Controller
{
    /**
     * 포인트 거래 로그 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserPointLog::query()->with(['user', 'admin']);

        // 검색 기능
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%")
                             ->orWhere('name', 'like', "%{$search}%");
                })
                ->orWhere('user_id', 'like', "%{$search}%")
                ->orWhere('reason', 'like', "%{$search}%")
                ->orWhere('reference_type', 'like', "%{$search}%")
                ->orWhere('reference_id', 'like', "%{$search}%");
            });
        }

        // 거래 유형 필터
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->get('transaction_type'));
        }

        // 참조 유형 필터
        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->get('reference_type'));
        }

        // 금액 범위 필터
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->get('amount_min'));
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->get('amount_max'));
        }

        // 날짜 범위 필터
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->get('date_to') . ' 23:59:59');
        }

        // 정렬
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $perPage = $request->get('per_page', 20);
        $logs = $query->paginate($perPage);

        // 통계 정보
        $statistics = [
            'total_logs' => UserPointLog::count(),
            'today_logs' => UserPointLog::whereDate('created_at', today())->count(),
            'transaction_types' => UserPointLog::select('transaction_type', DB::raw('count(*) as count'))
                                    ->groupBy('transaction_type')
                                    ->orderBy('count', 'desc')
                                    ->get(),
            'reference_types' => UserPointLog::select('reference_type', DB::raw('count(*) as count'))
                                  ->whereNotNull('reference_type')
                                  ->groupBy('reference_type')
                                  ->orderBy('count', 'desc')
                                  ->limit(10)
                                  ->get(),
            'daily_stats' => UserPointLog::select(
                                DB::raw('DATE(created_at) as date'),
                                DB::raw('count(*) as count'),
                                DB::raw('SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_earned'),
                                DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as total_used')
                            )
                            ->where('created_at', '>=', now()->subDays(7))
                            ->groupBy('date')
                            ->orderBy('date', 'desc')
                            ->get(),
            'monthly_summary' => [
                'earned' => UserPointLog::where('amount', '>', 0)
                    ->whereMonth('created_at', now()->month)
                    ->sum('amount'),
                'used' => UserPointLog::where('amount', '<', 0)
                    ->whereMonth('created_at', now()->month)
                    ->sum(DB::raw('ABS(amount)')),
            ],
        ];

        return view('jiny-emoney::point.log', [
            'logs' => $logs,
            'statistics' => $statistics,
            'request' => $request,
        ]);
    }
}