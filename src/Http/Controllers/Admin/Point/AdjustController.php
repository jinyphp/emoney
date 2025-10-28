<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 관리자 - 포인트 조정 컨트롤러
 */
class AdjustController extends Controller
{
    /**
     * 회원 포인트 조정 (지급/차감)
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer',
            'member_uuid' => 'required|string',
            'amount' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:255',
            'reference_type' => 'required|string'
        ]);

        $memberId = $request->input('member_id');
        $memberUuid = $request->input('member_uuid');
        $amount = $request->input('amount');
        $reason = $request->input('reason');
        $referenceType = $request->input('reference_type');

        // 현재 관리자 정보
        $admin = Auth::user();

        try {
            DB::beginTransaction();

            // 회원 존재 확인
            $user = DB::table('users')->where('id', $memberId)->first();
            if (!$user) {
                throw new \Exception('사용자를 찾을 수 없습니다.');
            }

            // 포인트 계정 조회 또는 생성
            $userPoint = DB::table('user_point')
                ->where('user_id', $memberId)
                ->first();

            if (!$userPoint) {
                // 포인트 계정 생성
                DB::table('user_point')->insert([
                    'user_id' => $memberId,
                    'user_uuid' => $memberUuid,
                    'balance' => 0,
                    'total_earned' => 0,
                    'total_used' => 0,
                    'total_expired' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $userPoint = (object) [
                    'user_id' => $memberId,
                    'user_uuid' => $memberUuid,
                    'balance' => 0,
                    'total_earned' => 0,
                    'total_used' => 0,
                    'total_expired' => 0
                ];
            }

            $oldBalance = $userPoint->balance;
            $newBalance = $oldBalance + $amount;

            // 잔액이 음수가 되는 경우 확인
            if ($newBalance < 0) {
                throw new \Exception('포인트 잔액이 부족합니다. (현재 잔액: ' . number_format($oldBalance) . 'P)');
            }

            // 포인트 계정 업데이트
            $updateData = [
                'balance' => $newBalance,
                'updated_at' => now()
            ];

            if ($amount > 0) {
                // 포인트 지급
                $updateData['total_earned'] = $userPoint->total_earned + $amount;
            } else {
                // 포인트 차감
                $updateData['total_used'] = $userPoint->total_used + abs($amount);
            }

            DB::table('user_point')
                ->where('user_id', $memberId)
                ->update($updateData);

            // 포인트 로그 생성
            $logData = [
                'user_id' => $memberId,
                'user_uuid' => $memberUuid,
                'transaction_type' => 'admin',
                'amount' => $amount,
                'balance_before' => $oldBalance,
                'balance_after' => $newBalance,
                'reason' => $reason,
                'reference_type' => $referenceType,
                'reference_id' => $admin->id ?? null,
                'admin_id' => $admin->id ?? null,
                'metadata' => json_encode([
                    'admin_action' => $amount > 0 ? 'grant' : 'deduct',
                    'admin_name' => $admin->name ?? 'System',
                    'admin_email' => $admin->email ?? '',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]),
                'expires_at' => $amount > 0 ? now()->addYear() : null, // 지급된 포인트는 1년 후 만료
                'created_at' => now(),
                'updated_at' => now()
            ];

            $logId = DB::table('user_point_log')->insertGetId($logData);

            // 지급된 포인트의 경우 만료 스케줄 등록
            if ($amount > 0) {
                DB::table('user_point_expiry')->insert([
                    'user_id' => $memberId,
                    'user_uuid' => $memberUuid,
                    'point_log_id' => $logId,
                    'amount' => $amount,
                    'expires_at' => now()->addYear(),
                    'expired' => false,
                    'notified' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            // 업데이트된 회원 정보 반환
            $updatedUser = DB::table('users')->where('id', $memberId)->first();

            return response()->json([
                'success' => true,
                'message' => '포인트 조정이 완료되었습니다.',
                'member' => [
                    'id' => $updatedUser->id,
                    'uuid' => $updatedUser->uuid ?? '',
                    'name' => $updatedUser->name ?? '',
                    'email' => $updatedUser->email,
                    'created_at' => $updatedUser->created_at
                ],
                'adjustment' => [
                    'amount' => $amount,
                    'old_balance' => $oldBalance,
                    'new_balance' => $newBalance,
                    'reason' => $reason,
                    'reference_type' => $referenceType
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Point adjustment error', [
                'member_id' => $memberId,
                'amount' => $amount,
                'admin_id' => $admin->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}