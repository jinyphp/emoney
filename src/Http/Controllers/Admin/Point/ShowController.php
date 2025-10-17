<?php

namespace Jiny\Auth\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Emoney\Models\UserPoint;
use Jiny\Auth\Emoney\Models\UserPointLog;

/**
 * 관리자 - 사용자 포인트 상세 보기 컨트롤러
 */
class ShowController extends Controller
{
    /**
     * 포인트 계정 상세 정보 표시
     */
    public function __invoke(Request $request, $id)
    {
        $userPoint = UserPoint::with('user')->findOrFail($id);

        // 포인트 로그 조회 (페이지네이션)
        $logsQuery = UserPointLog::where('user_id', $userPoint->user_id)
            ->orderBy('created_at', 'desc');

        // 거래 유형 필터
        if ($request->filled('transaction_type')) {
            $logsQuery->where('transaction_type', $request->transaction_type);
        }

        // 날짜 범위 필터
        if ($request->filled('date_from')) {
            $logsQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $logsQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $logsQuery->paginate(20);

        // 만료 예정 포인트 조회
        $expiringPoints = $userPoint->getExpiringPoints(30);

        // 통계 정보
        $statistics = [
            'total_transactions' => UserPointLog::where('user_id', $userPoint->user_id)->count(),
            'transactions_this_month' => UserPointLog::where('user_id', $userPoint->user_id)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'earned_this_month' => UserPointLog::where('user_id', $userPoint->user_id)
                ->where('transaction_type', 'earn')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'used_this_month' => UserPointLog::where('user_id', $userPoint->user_id)
                ->where('transaction_type', 'use')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'expiring_soon' => $expiringPoints->sum('amount'),
        ];

        return view('jiny-emoney::admin.point.show', [
            'userPoint' => $userPoint,
            'logs' => $logs,
            'expiringPoints' => $expiringPoints,
            'statistics' => $statistics,
            'request' => $request,
            'transactionTypes' => [
                'earn' => '적립',
                'use' => '사용',
                'refund' => '환불',
                'expire' => '만료',
                'admin' => '관리자 조정',
            ],
        ]);
    }
}