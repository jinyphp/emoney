<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Emoney\Models\UserPoint;

/**
 * 관리자 - 사용자 포인트 업데이트 컨트롤러
 */
class UpdateController extends Controller
{
    /**
     * 포인트 계정 업데이트 (포인트 조정)
     */
    public function __invoke(Request $request, $id)
    {
        $userPoint = UserPoint::findOrFail($id);

        $request->validate([
            'adjustment_type' => 'required|in:earn,use,admin_add,admin_subtract,refund,expire',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);

        DB::beginTransaction();

        try {
            $amount = $request->amount;
            $reason = $request->reason ?: '관리자 포인트 조정';
            $adminId = auth()->id();

            switch ($request->adjustment_type) {
                case 'earn':
                    $userPoint->earnPoints(
                        $amount,
                        $reason,
                        'admin_adjustment',
                        null,
                        $request->expires_at,
                        $adminId
                    );
                    $message = "{$amount} 포인트가 적립되었습니다.";
                    break;

                case 'use':
                    $userPoint->usePoints(
                        $amount,
                        $reason,
                        'admin_adjustment',
                        null,
                        $adminId
                    );
                    $message = "{$amount} 포인트가 사용 처리되었습니다.";
                    break;

                case 'admin_add':
                    $userPoint->adminAdjustment(
                        $amount,
                        $reason,
                        $adminId
                    );
                    $message = "관리자 권한으로 {$amount} 포인트가 추가되었습니다.";
                    break;

                case 'admin_subtract':
                    $userPoint->adminAdjustment(
                        -$amount,
                        $reason,
                        $adminId
                    );
                    $message = "관리자 권한으로 {$amount} 포인트가 차감되었습니다.";
                    break;

                case 'refund':
                    $userPoint->refundPoints(
                        $amount,
                        $reason,
                        'admin_adjustment',
                        null,
                        $adminId
                    );
                    $message = "{$amount} 포인트가 환불되었습니다.";
                    break;

                case 'expire':
                    $userPoint->expirePoints(
                        $amount,
                        $reason,
                        $adminId
                    );
                    $message = "{$amount} 포인트가 만료 처리되었습니다.";
                    break;
            }

            DB::commit();

            return redirect()
                ->route('admin.auth.point.show', $userPoint->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', '포인트 조정 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}