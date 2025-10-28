<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Emoney;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 관리자 - 이머니 충전 승인 컨트롤러
 */
class ApproveDepositController extends Controller
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

            DB::commit();

            return redirect()->route('admin.auth.emoney.deposits.index')
                ->with('success', '충전 신청이 승인되었습니다. 사용자 잔액이 업데이트되었습니다.');

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->with('error', '충전 승인 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}