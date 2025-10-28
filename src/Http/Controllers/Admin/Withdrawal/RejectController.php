<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Withdrawal;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 관리자 - 출금 신청 거부
 */
class RejectController extends Controller
{
    /**
     * 출금 신청 거부 처리
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

            // 거부 사유 검증
            $request->validate([
                'admin_memo' => 'required|string|max:500',
            ], [
                'admin_memo.required' => '거부 사유를 입력해주세요.',
                'admin_memo.max' => '거부 사유는 500자 이내로 입력해주세요.',
            ]);

            // 출금 신청 거부 처리
            DB::table('user_emoney_withdrawals')
                ->where('id', $withdrawalId)
                ->update([
                    'status' => 'rejected',
                    'checked' => '0',
                    'checked_at' => now(),
                    'checked_by' => auth()->id(),
                    'admin_memo' => $request->input('admin_memo'),
                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()->route('admin.auth.emoney.withdrawals.index')
                ->with('success', '출금 신청이 거부되었습니다.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->with('error', '출금 거부 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}