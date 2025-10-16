<?php

namespace Jiny\AuthEmoney\Http\Controllers\Emoney;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\AuthEmoney\Models\UserEmoneyLog;

/**
 * 관리자 - 사용자 이머니 거래 로그 관리 컨트롤러
 */
class LogController extends Controller
{
    /**
     * 사용자 이머니 거래 로그 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserEmoneyLog::query();

        // 검색 기능
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('user_id', 'like', "%{$search}%")
                  ->orWhere('trans_id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 상태 필터
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // 거래 유형 필터
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // 통화 필터
        if ($request->filled('currency')) {
            $query->where('currency', $request->get('currency'));
        }

        // 날짜 범위 필터
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->get('date_to') . ' 23:59:59');
        }

        // 거래 테이블 필터
        if ($request->filled('trans')) {
            $query->where('trans', $request->get('trans'));
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
            'total_logs' => UserEmoneyLog::count(),
            'today_logs' => UserEmoneyLog::whereDate('created_at', today())->count(),
            'transaction_types' => UserEmoneyLog::select('type', DB::raw('count(*) as count'))
                                    ->groupBy('type')
                                    ->orderBy('count', 'desc')
                                    ->get(),
            'daily_stats' => UserEmoneyLog::select(
                                DB::raw('DATE(created_at) as date'),
                                DB::raw('count(*) as count'),
                                DB::raw('SUM(CASE WHEN deposit IS NOT NULL THEN deposit ELSE 0 END) as total_deposit'),
                                DB::raw('SUM(CASE WHEN withdraw IS NOT NULL THEN withdraw ELSE 0 END) as total_withdraw')
                            )
                            ->where('created_at', '>=', now()->subDays(7))
                            ->groupBy('date')
                            ->orderBy('date', 'desc')
                            ->get(),
        ];

        return view('jiny-auth-emoney::emoney.log', [
            'logs' => $logs,
            'statistics' => $statistics,
            'request' => $request,
        ]);
    }
}