<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Deposit;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Facades\Shard;

/**
 * 관리자 - 충전 신청 승인
 */
class ApproveController extends Controller
{
    /**
     * 충전 신청 승인 처리
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

            // 충전 신청 승인 처리
            DB::table('user_emoney_deposits')
                ->where('id', $depositId)
                ->update([
                    'status' => 'approved',
                    'checked' => '1',
                    'checked_at' => now(),
                    'checked_by' => auth()->id(),
                    'admin_memo' => $request->input('admin_memo', ''),
                    'updated_at' => now(),
                ]);

            // 사용자 이머니 잔액 업데이트
            $userUuid = $deposit->user_uuid;
            $amount = $deposit->amount;

            // 기존 이머니 정보 조회
            $userEmoney = DB::table('user_emoney')
                ->where('user_uuid', $userUuid)
                ->first();

            if ($userEmoney) {
                // 기존 잔액에 충전 금액 추가
                DB::table('user_emoney')
                    ->where('user_uuid', $userUuid)
                    ->update([
                        'balance' => $userEmoney->balance + $amount,
                        'total_deposit' => ($userEmoney->total_deposit ?? 0) + $amount,
                        'updated_at' => now(),
                    ]);
            } else {
                // 새로운 이머니 지갑 생성
                DB::table('user_emoney')->insert([
                    'user_uuid' => $userUuid,
                    'balance' => $amount,
                    'total_deposit' => $amount,
                    'total_used' => 0,
                    'points' => 0,
                    'currency' => 'KRW',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 이머니 거래 로그 기록
            DB::table('user_emoney_logs')->insert([
                'user_uuid' => $userUuid,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $userEmoney->balance ?? 0,
                'balance_after' => ($userEmoney->balance ?? 0) + $amount,
                'description' => '충전 승인: ' . ($request->input('admin_memo') ?: '관리자 승인'),
                'reference_id' => $depositId,
                'reference_type' => 'deposit',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 사용자에게 승인 알림 발송
            $this->sendApprovalNotification($deposit, $amount, $request->input('admin_memo'));

            DB::commit();

            return redirect()->route('admin.auth.emoney.deposits.index')
                ->with('success', '충전 신청이 승인되었습니다. 사용자 잔액이 업데이트되었습니다.');

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->with('error', '충전 승인 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 사용자에게 승인 알림 발송
     */
    private function sendApprovalNotification($deposit, $amount, $adminMemo = null)
    {
        try {
            // 사용자 정보 조회
            $user = Shard::getUserByUuid($deposit->user_uuid);

            if (!$user) {
                \Log::warning('User not found for approval notification', [
                    'user_uuid' => $deposit->user_uuid
                ]);
                return;
            }

            // 알림 메시지 생성
            $message = sprintf(
                '충전 신청 금액: %s원\n이머니가 성공적으로 충전되었습니다.',
                number_format($amount)
            );

            if ($adminMemo) {
                $message .= "\n관리자 메모: " . $adminMemo;
            }

            $message .= "\n\n이머니 잔액을 확인해 보세요!";

            // 알림 데이터 생성
            $notificationData = [
                'user_uuid' => $deposit->user_uuid,
                'email' => $user->email,
                'name' => $user->name,
                'type' => 'emoney_deposit_approved',
                'title' => '이머니 충전이 완료되었습니다',
                'message' => $message,
                'data' => json_encode([
                    'deposit_id' => $deposit->id,
                    'amount' => $amount,
                    'bank_name' => $deposit->bank_name,
                    'depositor_name' => $deposit->depositor_name,
                    'admin_memo' => $adminMemo,
                    'approved_at' => now()->toISOString()
                ]),
                'action_url' => route('home.emoney.index'),
                'action_text' => '이머니 확인하기',
                'status' => 'unread',
                'priority' => 'normal',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 알림 저장
            DB::table('user_notifications')->insert($notificationData);

            \Log::info('Approval notification sent', [
                'user_uuid' => $deposit->user_uuid,
                'deposit_id' => $deposit->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send approval notification', [
                'user_uuid' => $deposit->user_uuid,
                'deposit_id' => $deposit->id,
                'error' => $e->getMessage()
            ]);
            // 알림 발송 실패는 메인 프로세스에 영향을 주지 않음
        }
    }
}