<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Withdrawal;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 관리자 - 출금 신청 승인
 */
class ApproveController extends Controller
{
    /**
     * 출금 신청 승인 처리
     */
    public function __invoke(Request $request, int $withdrawalId)
    {
        try {
            DB::beginTransaction();

            // 출금 신청 정보 조회
            $withdrawal = DB::table('user_emoney_withdrawals')
                ->where('id', $withdrawalId)
                ->where('status', 'pending')
                ->first();

            if (!$withdrawal) {
                return redirect()->back()
                    ->with('error', '출금 신청을 찾을 수 없거나 이미 처리되었습니다.');
            }

            // 사용자 이머니 잔액 확인
            $userUuid = $withdrawal->user_uuid;
            $amount = $withdrawal->amount;
            $fee = $withdrawal->fee ?? 0;
            $totalAmount = $amount + $fee;

            $userEmoney = DB::table('user_emoney')
                ->where('user_uuid', $userUuid)
                ->first();

            if (!$userEmoney) {
                return redirect()->back()
                    ->with('error', '사용자의 이머니 계정을 찾을 수 없습니다.');
            }

            // 잔액 부족 확인
            if ($userEmoney->balance < $totalAmount) {
                return redirect()->back()
                    ->with('error', '사용자의 잔액이 부족합니다. (현재 잔액: ' . number_format($userEmoney->balance) . '원, 필요 금액: ' . number_format($totalAmount) . '원)');
            }

            // 출금 신청 승인 처리
            DB::table('user_emoney_withdrawals')
                ->where('id', $withdrawalId)
                ->update([
                    'status' => 'approved',
                    'checked' => '1',
                    'checked_at' => now(),
                    'checked_by' => auth()->id(),
                    'admin_memo' => $request->input('admin_memo', ''),
                    'updated_at' => now(),
                ]);

            // 사용자 이머니 잔액 차감
            $newBalance = $userEmoney->balance - $totalAmount;
            $newTotalWithdrawn = ($userEmoney->total_withdrawn ?? 0) + $totalAmount;

            DB::table('user_emoney')
                ->where('user_uuid', $userUuid)
                ->update([
                    'balance' => $newBalance,
                    'total_withdrawn' => $newTotalWithdrawn,
                    'updated_at' => now(),
                ]);

            // 이머니 거래 로그 기록
            DB::table('user_emoney_logs')->insert([
                'user_uuid' => $userUuid,
                'type' => 'withdrawal',
                'amount' => -$totalAmount, // 음수로 기록
                'balance_before' => $userEmoney->balance,
                'balance_after' => $newBalance,
                'description' => '출금 승인: ' . ($request->input('admin_memo') ?: '관리자 승인') .
                               ' (출금액: ' . number_format($amount) . '원, 수수료: ' . number_format($fee) . '원)',
                'reference_id' => $withdrawalId,
                'reference_type' => 'withdrawal',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.auth.emoney.withdrawals.index')
                ->with('success', '출금 신청이 승인되었습니다. 사용자 잔액에서 ' . number_format($totalAmount) . '원이 차감되었습니다.');

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->with('error', '출금 승인 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}