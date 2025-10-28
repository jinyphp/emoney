<?php

namespace Jiny\Emoney\Http\Controllers\Emoney\Deposit;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 이머니 충전 취소 요청
 */
class CancelController extends Controller
{
    use JWTAuthTrait;

    /**
     * 충전 취소 요청 처리
     */
    public function __invoke(Request $request, int $depositId)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '로그인이 필요합니다.'
            ], 401);
        }

        // 취소 사유 검증
        $request->validate([
            'cancel_reason' => 'required|string|max:500',
            'refund_account_id' => 'required|exists:user_emoney_bank,id',
        ], [
            'cancel_reason.required' => '취소 사유를 입력해주세요.',
            'cancel_reason.max' => '취소 사유는 500자 이내로 입력해주세요.',
            'refund_account_id.required' => '환불 받을 계좌를 선택해주세요.',
            'refund_account_id.exists' => '선택한 계좌가 유효하지 않습니다.',
        ]);

        $userUuid = $user->uuid;

        try {
            DB::beginTransaction();

            // 충전 신청 정보 조회 (본인 신청이고 취소 가능한 상태인지 확인)
            $deposit = DB::table('user_emoney_deposits')
                ->where('id', $depositId)
                ->where('user_uuid', $userUuid)
                ->where('status', 'pending') // 대기 상태만 취소 가능
                ->first();

            if (!$deposit) {
                throw new \Exception('취소할 수 없는 충전 신청입니다.');
            }

            // 환불 계좌 정보 확인 (본인 계좌인지 검증)
            $refundAccount = DB::table('user_emoney_bank')
                ->where('id', $request->input('refund_account_id'))
                ->where('user_uuid', $userUuid)
                ->where('enable', true)
                ->first();

            if (!$refundAccount) {
                throw new \Exception('유효하지 않은 환불 계좌입니다.');
            }

            // 충전 신청 상태를 취소로 변경
            DB::table('user_emoney_deposits')
                ->where('id', $depositId)
                ->update([
                    'status' => 'cancelled',
                    'cancel_reason' => $request->input('cancel_reason'),
                    'refund_account_id' => $request->input('refund_account_id'),
                    'refund_bank_name' => $refundAccount->bank_name,
                    'refund_account_number' => $refundAccount->account_number,
                    'refund_account_holder' => $refundAccount->account_holder,
                    'cancelled_at' => now(),
                    'updated_at' => now(),
                ]);

            // 취소 로그 기록
            DB::table('user_emoney_logs')->insert([
                'user_uuid' => $userUuid,
                'type' => 'deposit_cancel',
                'amount' => -$deposit->amount, // 음수로 기록
                'balance_before' => 0,
                'balance_after' => 0,
                'description' => '충전 취소 요청: ' . $request->input('cancel_reason'),
                'reference_id' => $depositId,
                'reference_type' => 'deposit',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '충전 취소 요청이 완료되었습니다. 관리자 확인 후 환불 처리됩니다.',
                'data' => [
                    'deposit_id' => $depositId,
                    'status' => 'cancelled',
                    'refund_account' => [
                        'bank_name' => $refundAccount->bank_name,
                        'account_number' => $refundAccount->account_number,
                        'account_holder' => $refundAccount->account_holder,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => '취소 요청 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}