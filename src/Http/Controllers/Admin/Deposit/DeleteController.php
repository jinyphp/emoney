<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Deposit;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Facades\Shard;

/**
 * 관리자 - 충전 신청 삭제
 */
class DeleteController extends Controller
{
    /**
     * 충전 신청 삭제 처리
     */
    public function __invoke(Request $request, $depositId)
    {
        DB::beginTransaction();

        try {
            // 충전 신청 정보 조회
            $deposit = DB::table('user_emoney_deposits')
                ->where('id', $depositId)
                ->first();

            if (!$deposit) {
                return response()->json([
                    'success' => false,
                    'message' => '해당 충전 신청을 찾을 수 없습니다.'
                ], 404);
            }

            // 승인된 충전은 삭제할 수 없음
            if ($deposit->status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => '이미 승인된 충전 신청은 삭제할 수 없습니다.'
                ], 400);
            }

            // 사용자 정보 조회 (로그용)
            $user = Shard::getUserByUuid($deposit->user_uuid);

            // 관련 로그 삭제
            DB::table('user_emoney_logs')
                ->where('reference_id', $depositId)
                ->where('reference_type', 'deposit')
                ->delete();

            // 충전 신청 삭제
            DB::table('user_emoney_deposits')
                ->where('id', $depositId)
                ->delete();

            // 관리자 로그 기록
            $adminUser = auth()->user();
            if ($adminUser) {
                DB::table('admin_user_logs')->insert([
                    'user_id' => $adminUser->id,
                    'action' => 'delete',
                    'email' => $adminUser->email,
                    'name' => $adminUser->name,
                    'event_type' => 'deposit_delete',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'logged_at' => now(),
                    'details' => json_encode([
                        'table_name' => 'user_emoney_deposits',
                        'record_id' => $depositId,
                        'deleted_data' => $deposit
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 시스템 로그 기록
            \Log::info('Deposit deleted by admin', [
                'deposit_id' => $depositId,
                'user_uuid' => $deposit->user_uuid,
                'user_name' => $user->name ?? 'N/A',
                'amount' => $deposit->amount,
                'status' => $deposit->status,
                'admin_user_id' => $adminUser->id ?? null,
                'admin_name' => $adminUser->name ?? 'System'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '충전 신청이 성공적으로 삭제되었습니다.',
                'data' => [
                    'deleted_deposit_id' => $depositId,
                    'user_name' => $user->name ?? 'N/A',
                    'amount' => number_format($deposit->amount)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Deposit deletion failed', [
                'deposit_id' => $depositId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '충전 신청 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}