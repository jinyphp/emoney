<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Deposit;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Facades\Shard;

/**
 * 관리자 - 충전 신청 거부
 */
class RejectController extends Controller
{
    /**
     * 충전 신청 거부 처리
     */
    public function __invoke(Request $request, int $depositId)
    {
        try {
            DB::beginTransaction();

            // 충전 신청 정보 조회
            $deposit = DB::table('user_emoney_deposits')
                ->where('id', $depositId)
                ->where('status', 'pending')
                ->first();

            if (!$deposit) {
                return redirect()->back()
                    ->with('error', '충전 신청을 찾을 수 없거나 이미 처리되었습니다.');
            }

            // 거부 사유 검증
            $request->validate([
                'admin_memo' => 'required|string|max:500',
            ], [
                'admin_memo.required' => '거부 사유를 입력해주세요.',
                'admin_memo.max' => '거부 사유는 500자 이내로 입력해주세요.',
            ]);

            // 충전 신청 거부 처리
            DB::table('user_emoney_deposits')
                ->where('id', $depositId)
                ->update([
                    'status' => 'rejected',
                    'checked' => '0',
                    'checked_at' => now(),
                    'checked_by' => auth()->id(),
                    'admin_memo' => $request->input('admin_memo'),
                    'updated_at' => now(),
                ]);

            // 사용자에게 거절 알림 발송
            $this->sendRejectionNotification($deposit, $request->input('admin_memo'));

            DB::commit();

            return redirect()->route('admin.auth.emoney.deposits.index')
                ->with('success', '충전 신청이 거부되었습니다.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->with('error', '충전 거부 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 사용자에게 거절 알림 발송
     */
    private function sendRejectionNotification($deposit, $adminMemo)
    {
        try {
            // 사용자 정보 조회
            $user = Shard::getUserByUuid($deposit->user_uuid);

            if (!$user) {
                \Log::warning('User not found for rejection notification', [
                    'user_uuid' => $deposit->user_uuid
                ]);
                return;
            }

            // 알림 데이터 생성
            $notificationData = [
                'user_uuid' => $deposit->user_uuid,
                'email' => $user->email,
                'name' => $user->name,
                'type' => 'emoney_deposit_rejected',
                'title' => '이머니 충전 신청이 거절되었습니다',
                'message' => sprintf(
                    '충전 신청 금액: %s원\n거절 사유: %s\n\n문의사항이 있으시면 고객센터로 연락해 주세요.',
                    number_format($deposit->amount),
                    $adminMemo
                ),
                'data' => json_encode([
                    'deposit_id' => $deposit->id,
                    'amount' => $deposit->amount,
                    'bank_name' => $deposit->bank_name,
                    'depositor_name' => $deposit->depositor_name,
                    'admin_memo' => $adminMemo,
                    'rejected_at' => now()->toISOString()
                ]),
                'action_url' => route('home.emoney.deposit.history'),
                'action_text' => '충전 내역 보기',
                'status' => 'unread',
                'priority' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 알림 저장
            DB::table('user_notifications')->insert($notificationData);

            \Log::info('Rejection notification sent', [
                'user_uuid' => $deposit->user_uuid,
                'deposit_id' => $deposit->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send rejection notification', [
                'user_uuid' => $deposit->user_uuid,
                'deposit_id' => $deposit->id,
                'error' => $e->getMessage()
            ]);
            // 알림 발송 실패는 메인 프로세스에 영향을 주지 않음
        }
    }
}