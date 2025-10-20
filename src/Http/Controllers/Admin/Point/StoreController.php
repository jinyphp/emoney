<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Emoney\Models\UserPoint;
use App\Models\User;

/**
 * 관리자 - 사용자 포인트 생성/저장 컨트롤러
 */
class StoreController extends Controller
{
    /**
     * 포인트 계정 생성 또는 포인트 조정
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'action_type' => 'required|in:create,adjust',
            'amount' => 'required_if:action_type,adjust|numeric|min:0.01',
            'adjustment_type' => 'required_if:action_type,adjust|in:earn,use,admin_add,admin_subtract',
            'reason' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($request->user_id);
            $userPoint = UserPoint::findOrCreateForUser($user->id);

            if ($request->action_type === 'create') {
                // 이미 계정이 존재한다면 메시지 반환
                if ($userPoint->wasRecentlyCreated) {
                    $message = "사용자 {$user->name}({$user->email})의 포인트 계정이 생성되었습니다.";
                } else {
                    $message = "사용자 {$user->name}({$user->email})의 포인트 계정이 이미 존재합니다.";
                }
            } else {
                // 포인트 조정
                $amount = $request->amount;
                $reason = $request->reason ?: '관리자 조정';
                $adminId = auth()->id();

                switch ($request->adjustment_type) {
                    case 'earn':
                        $userPoint->earnPoints(
                            $amount,
                            $reason,
                            'admin_manual',
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
                            'admin_manual',
                            null,
                            $adminId
                        );
                        $message = "{$amount} 포인트가 차감되었습니다.";
                        break;

                    case 'admin_add':
                        $userPoint->adminAdjustment(
                            $amount,
                            $reason,
                            $adminId
                        );
                        $message = "관리자 권한으로 {$amount} 포인트가 지급되었습니다.";
                        break;

                    case 'admin_subtract':
                        $userPoint->adminAdjustment(
                            -$amount,
                            $reason,
                            $adminId
                        );
                        $message = "관리자 권한으로 {$amount} 포인트가 차감되었습니다.";
                        break;
                }
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
                ->with('error', '포인트 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}