<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Emoney\Models\UserPointExpiry;

/**
 * 관리자 - 포인트 만료 관리 컨트롤러
 */
class ExpiryController extends Controller
{
    /**
     * 포인트 만료 스케줄 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserPointExpiry::query()->with(['user', 'pointLog']);

        // 검색 기능
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%")
                             ->orWhere('name', 'like', "%{$search}%");
                })
                ->orWhere('user_id', 'like', "%{$search}%");
            });
        }

        // 만료 상태 필터
        if ($request->filled('expired')) {
            $query->where('expired', $request->get('expired') == '1');
        }

        // 알림 상태 필터
        if ($request->filled('notified')) {
            $query->where('notified', $request->get('notified') == '1');
        }

        // 만료일 범위 필터
        if ($request->filled('expires_from')) {
            $query->where('expires_at', '>=', $request->get('expires_from'));
        }
        if ($request->filled('expires_to')) {
            $query->where('expires_at', '<=', $request->get('expires_to') . ' 23:59:59');
        }

        // 만료 임박 필터
        if ($request->filled('expiring_days')) {
            $days = (int)$request->get('expiring_days');
            $query->where('expired', false)
                  ->where('expires_at', '<=', now()->addDays($days))
                  ->where('expires_at', '>', now());
        }

        // 금액 범위 필터
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->get('amount_min'));
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->get('amount_max'));
        }

        // 정렬
        $sortBy = $request->get('sort_by', 'expires_at');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $perPage = $request->get('per_page', 20);
        $expiries = $query->paginate($perPage);

        // 통계 정보
        $statistics = [
            'total_schedules' => UserPointExpiry::count(),
            'pending_expiries' => UserPointExpiry::where('expired', false)->count(),
            'expired_count' => UserPointExpiry::where('expired', true)->count(),
            'total_pending_amount' => UserPointExpiry::where('expired', false)->sum('amount'),
            'total_expired_amount' => UserPointExpiry::where('expired', true)->sum('amount'),
            'expiring_today' => UserPointExpiry::where('expired', false)
                ->whereDate('expires_at', today())
                ->count(),
            'expiring_this_week' => UserPointExpiry::where('expired', false)
                ->whereBetween('expires_at', [now(), now()->addWeek()])
                ->count(),
            'expiring_this_month' => UserPointExpiry::where('expired', false)
                ->whereBetween('expires_at', [now(), now()->addMonth()])
                ->count(),
            'notification_pending' => UserPointExpiry::where('expired', false)
                ->where('notified', false)
                ->where('expires_at', '<=', now()->addDays(7))
                ->count(),
            'monthly_expiry_schedule' => UserPointExpiry::select(
                DB::raw('DATE_FORMAT(expires_at, "%Y-%m") as month'),
                DB::raw('count(*) as count'),
                DB::raw('sum(amount) as total_amount')
            )
                ->where('expired', false)
                ->where('expires_at', '>=', now())
                ->groupBy('month')
                ->orderBy('month')
                ->limit(12)
                ->get(),
        ];

        return view('jiny-emoney::point.expiry', [
            'expiries' => $expiries,
            'statistics' => $statistics,
            'request' => $request,
        ]);
    }
}