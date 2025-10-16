<?php

namespace Jiny\Auth\Emoney\Http\Controllers\Emoney;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\AuthEmoney\Models\UserEmoneyDeposit;

/**
 * 관리자 - 사용자 입금 관리 컨트롤러
 */
class DepositController extends Controller
{
    /**
     * 사용자 입금 내역 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserEmoneyDeposit::query();

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

        // 통화 필터
        if ($request->filled('currency')) {
            $query->where('currency', $request->get('currency'));
        }

        // 확인 상태 필터
        if ($request->filled('checked')) {
            $query->where('checked', $request->get('checked'));
        }

        // 은행 필터
        if ($request->filled('bank')) {
            $query->where('bank', $request->get('bank'));
        }

        // 날짜 범위 필터
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->get('date_to') . ' 23:59:59');
        }

        // 금액 범위 필터
        if ($request->filled('amount_from')) {
            $query->where('amount', '>=', $request->get('amount_from'));
        }
        if ($request->filled('amount_to')) {
            $query->where('amount', '<=', $request->get('amount_to'));
        }

        // 정렬
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $perPage = $request->get('per_page', 20);
        $deposits = $query->paginate($perPage);

        // 통계 정보
        $statistics = [
            'total_deposits' => UserEmoneyDeposit::count(),
            'pending_deposits' => UserEmoneyDeposit::where('checked', null)->count(),
            'approved_deposits' => UserEmoneyDeposit::where('checked', '1')->count(),
            'total_amount' => UserEmoneyDeposit::where('checked', '1')->sum('amount'),
            'today_deposits' => UserEmoneyDeposit::whereDate('created_at', today())->count(),
            'today_amount' => UserEmoneyDeposit::whereDate('created_at', today())->where('checked', '1')->sum('amount'),
        ];

        return view('jiny-emoney::emoney.deposit', [
            'deposits' => $deposits,
            'statistics' => $statistics,
            'request' => $request,
        ]);
    }
}