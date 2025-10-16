<?php

namespace Jiny\AuthEmoney\Http\Controllers\Emoney;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\AuthEmoney\Models\UserEmoney;

/**
 * 관리자 - 사용자 이머니 메인 관리 컨트롤러
 */
class IndexController extends Controller
{
    /**
     * 사용자 이머니 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserEmoney::query();

        // 검색 기능
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('user_id', 'like', "%{$search}%");
            });
        }

        // 상태 필터
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // 통화 필터
        if ($request->filled('currency')) {
            $query->where('currency', $request->get('currency'));
        }

        // 정렬
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $perPage = $request->get('per_page', 20);
        $emoneys = $query->paginate($perPage);

        // 통계 정보
        $statistics = [
            'total_users' => UserEmoney::count(),
            'active_users' => UserEmoney::where('status', 'active')->count(),
            'total_balance' => UserEmoney::sum('balance'),
            'total_points' => UserEmoney::sum('point'),
        ];

        return view('jiny-auth-emoney::emoney.index', [
            'emoneys' => $emoneys,
            'statistics' => $statistics,
            'request' => $request,
        ]);
    }
}