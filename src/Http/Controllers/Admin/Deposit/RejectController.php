<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Deposit;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Facades\Shard;

/**
 * 관리자 - 충전 신청 거부
 *
 * [메소드 호출 관계 트리]
 * RejectController
 * ├── __invoke(Request $request, int $depositId)
 * │   ├── DB::beginTransaction() - 트랜잭션 시작
 * │   ├── 충전 신청 검증
 * │   │   ├── DB::table('user_emoney_deposits')->where()->first() - 충전 신청 조회
 * │   │   └── 상태 및 존재 여부 확인 (pending 상태만 처리)
 * │   ├── 충전 신청 상태 업데이트
 * │   │   └── DB::table('user_emoney_deposits')->update() - 거부 상태로 변경
 * │   ├── sendRejectionNotification($deposit, $adminMemo) - 거부 알림 발송
 * │   ├── DB::commit() - 트랜잭션 커밋
 * │   └── redirect()->route('admin.auth.emoney.deposits.index') - 성공 리다이렉트
 * └── sendRejectionNotification($deposit, $adminMemo)
 *     ├── Shard::getUserByUuid($deposit->user_uuid) - 사용자 정보 조회
 *     ├── 거부 알림 메시지 생성
 *     ├── JSON 데이터 구성
 *     └── DB::table('user_notifications')->insert() - 알림 저장
 *
 * [컨트롤러 역할]
 * - 대기 중인 충전 신청을 거부 처리
 * - 이머니 잔액 변경 없이 상태만 rejected로 변경
 * - 거부 사유를 관리자 메모로 기록
 * - 사용자에게 거부 완료 알림 발송
 *
 * [ApproveController와의 차이점]
 * - 이머니 잔액 업데이트 없음
 * - 거래 로그 기록 없음
 * - 상태만 rejected로 변경
 * - 거부 사유와 함께 알림 발송
 *
 * [라우트 연결]
 * Route: POST /admin/auth/emoney/deposits/{id}/reject
 * Name: admin.auth.emoney.deposits.reject
 *
 * [관련 컨트롤러]
 * - IndexController: 거부 완료 후 목록으로 리다이렉트
 * - ApproveController: 승인 처리 (대안 액션)
 * - ShowController: 상세보기에서 거부 버튼 클릭
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