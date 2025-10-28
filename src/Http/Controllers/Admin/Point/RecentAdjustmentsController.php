<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 관리자 - 최근 포인트 조정 내역 컨트롤러
 */
class RecentAdjustmentsController extends Controller
{
    /**
     * 특정 회원의 최근 관리자 조정 내역 조회
     */
    public function __invoke(Request $request, $memberId)
    {
        try {
            // 최근 30일간의 관리자 조정 내역 조회
            $adjustments = DB::table('user_point_log as upl')
                ->leftJoin('users as admin', 'upl.admin_id', '=', 'admin.id')
                ->where('upl.user_id', $memberId)
                ->where('upl.transaction_type', 'admin')
                ->where('upl.created_at', '>=', now()->subDays(30))
                ->select([
                    'upl.id',
                    'upl.amount',
                    'upl.reason',
                    'upl.reference_type',
                    'upl.balance_before',
                    'upl.balance_after',
                    'upl.created_at',
                    'admin.name as admin_name',
                    'admin.email as admin_email'
                ])
                ->orderBy('upl.created_at', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'adjustments' => $adjustments->map(function ($adjustment) {
                    return [
                        'id' => $adjustment->id,
                        'amount' => $adjustment->amount,
                        'reason' => $adjustment->reason,
                        'reference_type' => $adjustment->reference_type,
                        'balance_before' => $adjustment->balance_before,
                        'balance_after' => $adjustment->balance_after,
                        'created_at' => $adjustment->created_at,
                        'admin_name' => $adjustment->admin_name,
                        'admin_email' => $adjustment->admin_email
                    ];
                })->toArray()
            ]);

        } catch (\Exception $e) {
            \Log::error('Recent adjustments query error', [
                'member_id' => $memberId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '조정 내역 조회 중 오류가 발생했습니다.',
                'adjustments' => []
            ], 500);
        }
    }
}