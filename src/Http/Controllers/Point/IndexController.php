<?php

namespace Jiny\AuthEmoney\Http\Controllers\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\AuthEmoney\Models\UserPoint;

/**
 * 관리자 - 사용자 포인트 메인 관리 컨트롤러
 */
class IndexController extends Controller
{
    /**
     * 사용자 포인트 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserPoint::query()->with('user');

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

        // 잔액 범위 필터
        if ($request->filled('balance_min')) {
            $query->where('balance', '>=', $request->get('balance_min'));
        }
        if ($request->filled('balance_max')) {
            $query->where('balance', '<=', $request->get('balance_max'));
        }

        // 정렬
        $sortBy = $request->get('sort_by', 'balance');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $perPage = $request->get('per_page', 20);
        $points = $query->paginate($perPage);

        // 통계 정보
        $statistics = [
            'total_users' => UserPoint::count(),
            'total_balance' => UserPoint::sum('balance'),
            'total_earned' => UserPoint::sum('total_earned'),
            'total_used' => UserPoint::sum('total_used'),
            'total_expired' => UserPoint::sum('total_expired'),
            'avg_balance' => UserPoint::avg('balance'),
            'users_with_balance' => UserPoint::where('balance', '>', 0)->count(),
            'top_holders' => UserPoint::with('user')
                ->orderBy('balance', 'desc')
                ->limit(5)
                ->get(),
        ];

        return view('jiny-auth-emoney::point.index', [
            'points' => $points,
            'statistics' => $statistics,
            'request' => $request,
        ]);
    }
}