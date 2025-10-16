<?php

namespace Jiny\AuthEmoney\Http\Controllers\Emoney\Bank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\AuthEmoney\Models\UserEmoneyBank;

/**
 * 관리자 - 사용자 은행 계좌 목록 컨트롤러
 */
class IndexController extends Controller
{
    /**
     * 사용자 은행 계좌 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserEmoneyBank::query();

        // 검색 기능
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('user_id', 'like', "%{$search}%")
                  ->orWhere('bank', 'like', "%{$search}%")
                  ->orWhere('account', 'like', "%{$search}%")
                  ->orWhere('owner', 'like', "%{$search}%");
            });
        }

        // 상태 필터
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // 은행 필터
        if ($request->filled('bank')) {
            $query->where('bank', $request->get('bank'));
        }

        // 활성화 상태 필터
        if ($request->filled('enable')) {
            $query->where('enable', $request->get('enable'));
        }

        // 기본 계좌 필터
        if ($request->filled('default')) {
            $query->where('default', $request->get('default'));
        }

        // 정렬
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $perPage = $request->get('per_page', 20);
        $banks = $query->paginate($perPage);

        // 통계 정보
        $statistics = [
            'total_accounts' => UserEmoneyBank::count(),
            'active_accounts' => UserEmoneyBank::where('enable', true)->where('status', 'active')->count(),
            'default_accounts' => UserEmoneyBank::where('default', true)->count(),
            'banks' => UserEmoneyBank::select('bank', DB::raw('count(*) as count'))
                        ->groupBy('bank')
                        ->orderBy('count', 'desc')
                        ->limit(10)
                        ->get(),
        ];

        return view('jiny-auth-emoney::emoney.bank.index', [
            'banks' => $banks,
            'statistics' => $statistics,
            'request' => $request,
        ]);
    }
}