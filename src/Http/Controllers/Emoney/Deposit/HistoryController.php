<?php

namespace Jiny\Emoney\Http\Controllers\Emoney\Deposit;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 이머니 충전 내역 조회
 */
class HistoryController extends Controller
{
    use JWTAuthTrait;

    /**
     * 충전 내역 조회
     */
    public function __invoke(Request $request)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $userUuid = $user->uuid;

        // 필터링 조건 설정
        $query = DB::table('user_emoney_deposits')
            ->where('user_uuid', $userUuid);

        // 상태 필터
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 날짜 범위 필터
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // 금액 범위 필터
        if ($request->filled('amount_from')) {
            $query->where('amount', '>=', $request->amount_from);
        }
        if ($request->filled('amount_to')) {
            $query->where('amount', '<=', $request->amount_to);
        }

        // 은행 필터
        if ($request->filled('bank')) {
            $query->where('bank_name', 'like', '%' . $request->bank . '%');
        }

        // 정렬 및 페이지네이션
        $deposits = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // 상태별 통계
        $statistics = [
            'total_count' => DB::table('user_emoney_deposits')
                ->where('user_uuid', $userUuid)
                ->count(),
            'pending_count' => DB::table('user_emoney_deposits')
                ->where('user_uuid', $userUuid)
                ->where('status', 'pending')
                ->count(),
            'approved_count' => DB::table('user_emoney_deposits')
                ->where('user_uuid', $userUuid)
                ->where('status', 'approved')
                ->count(),
            'total_approved_amount' => DB::table('user_emoney_deposits')
                ->where('user_uuid', $userUuid)
                ->where('status', 'approved')
                ->sum('amount'),
            'this_month_count' => DB::table('user_emoney_deposits')
                ->where('user_uuid', $userUuid)
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
                ->count(),
            'this_month_amount' => DB::table('user_emoney_deposits')
                ->where('user_uuid', $userUuid)
                ->where('status', 'approved')
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
                ->sum('amount'),
        ];

        // 상태별 색상 매핑
        $statusColors = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
            'refunded' => 'info',
        ];

        // 상태별 텍스트 매핑
        $statusTexts = [
            'pending' => '승인 대기',
            'approved' => '승인 완료',
            'rejected' => '승인 거부',
            'cancelled' => '취소 요청',
            'refunded' => '환불 완료',
        ];

        return view('jiny-emoney::home.deposit.history', [
            'user' => $user,
            'deposits' => $deposits,
            'statistics' => $statistics,
            'statusColors' => $statusColors,
            'statusTexts' => $statusTexts,
            'request' => $request,
        ]);
    }
}