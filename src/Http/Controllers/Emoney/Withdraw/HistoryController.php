<?php

namespace Jiny\Emoney\Http\Controllers\Emoney\Withdraw;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 이머니 출금 내역
 */
class HistoryController extends Controller
{
    use JWTAuthTrait;

    public function __invoke(Request $request)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $userUuid = $user->uuid;

        // 필터링 파라미터
        $status = $request->get('status', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');
        $perPage = $request->get('per_page', 10);

        // 출금 내역 조회
        $query = DB::table('user_emoney_withdrawals')
            ->where('user_uuid', $userUuid);

        // 상태 필터
        if ($status) {
            $query->where('status', $status);
        }

        // 날짜 필터
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // 페이지네이션을 위한 총 개수 및 데이터 조회
        $totalCount = $query->count();
        $withdrawals = $query->orderBy('created_at', 'desc')
            ->offset(($request->get('page', 1) - 1) * $perPage)
            ->limit($perPage)
            ->get();

        // 페이지네이션 정보 계산
        $currentPage = $request->get('page', 1);
        $totalPages = ceil($totalCount / $perPage);

        // 통계 정보 조회
        $stats = [
            'total_requests' => DB::table('user_emoney_withdrawals')->where('user_uuid', $userUuid)->count(),
            'pending_requests' => DB::table('user_emoney_withdrawals')->where('user_uuid', $userUuid)->where('status', 'pending')->count(),
            'approved_requests' => DB::table('user_emoney_withdrawals')->where('user_uuid', $userUuid)->where('status', 'approved')->count(),
            'rejected_requests' => DB::table('user_emoney_withdrawals')->where('user_uuid', $userUuid)->where('status', 'rejected')->count(),
            'total_withdrawn' => DB::table('user_emoney_withdrawals')->where('user_uuid', $userUuid)->where('status', 'approved')->sum('amount') ?? 0,
            'total_fees' => DB::table('user_emoney_withdrawals')->where('user_uuid', $userUuid)->where('status', 'approved')->sum('fee') ?? 0,
        ];

        // 사용자 이머니 정보
        $emoney = DB::table('user_emoney')->where('user_uuid', $userUuid)->first();

        return view('jiny-emoney::home.withdraw.history', [
            'user' => $user,
            'emoney' => $emoney,
            'withdrawals' => $withdrawals,
            'statistics' => $stats,
            'filters' => [
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage
            ],
            'pagination' => [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'total_count' => $totalCount,
                'per_page' => $perPage,
                'has_prev' => $currentPage > 1,
                'has_next' => $currentPage < $totalPages,
                'prev_page' => max(1, $currentPage - 1),
                'next_page' => min($totalPages, $currentPage + 1)
            ]
        ]);
    }
}