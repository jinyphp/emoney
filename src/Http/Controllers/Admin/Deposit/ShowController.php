<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Deposit;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Facades\Shard;

/**
 * 관리자 - 충전 신청 상세 보기
 */
class ShowController extends Controller
{
    /**
     * 충전 신청 상세 정보 조회
     */
    public function __invoke(Request $request, $depositId)
    {
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

            // 사용자 정보 조회
            $user = Shard::getUserByUuid($deposit->user_uuid);
            $deposit->user_name = $user->name ?? 'N/A';
            $deposit->user_email = $user->email ?? 'N/A';

            // 관련 은행 정보 조회
            $bank = null;
            if ($deposit->bank_code) {
                $bank = DB::table('auth_banks')
                    ->where('code', $deposit->bank_code)
                    ->first();
            }

            // 충전 로그 조회
            $logs = DB::table('user_emoney_logs')
                ->where('reference_id', $depositId)
                ->where('reference_type', 'deposit')
                ->orderBy('created_at', 'desc')
                ->get();

            // 관리자 처리 로그 조회 (승인/거절 기록)
            $adminLogs = DB::table('admin_user_logs')
                ->where('table_name', 'user_emoney_deposits')
                ->where('record_id', $depositId)
                ->orderBy('created_at', 'desc')
                ->get();

            // AJAX 요청인 경우 JSON 응답
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'deposit' => $deposit,
                        'user' => $user,
                        'bank' => $bank,
                        'logs' => $logs,
                        'admin_logs' => $adminLogs
                    ]
                ]);
            }

            // 일반 요청인 경우 뷰 반환
            return view('jiny-emoney::admin.deposit.show', [
                'deposit' => $deposit,
                'user' => $user,
                'bank' => $bank,
                'logs' => $logs,
                'admin_logs' => $adminLogs
            ]);

        } catch (\Exception $e) {
            \Log::error('Deposit show failed', [
                'deposit_id' => $depositId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '충전 신청 정보를 불러오는 중 오류가 발생했습니다.'
                ], 500);
            }

            return back()->with('error', '충전 신청 정보를 불러오는 중 오류가 발생했습니다.');
        }
    }
}