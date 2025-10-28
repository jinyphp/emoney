<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Deposit;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Facades\Shard;

/**
 * 관리자 - 충전 신청 상세 보기
 *
 * [메소드 호출 관계 트리]
 * ShowController
 * └── __invoke(Request $request, $depositId)
 *     ├── DB::table('user_emoney_deposits')->where('id', $depositId)->first() - 충전 신청 조회
 *     ├── Shard::getUserByUuid($deposit->user_uuid) - 사용자 정보 조회
 *     ├── DB::table('user_emoney')->where('user_uuid')->first() - 사용자 이머니 정보 조회
 *     ├── DB::table('user_emoney_logs')->where() - 관련 거래 내역 조회
 *     └── view('jiny-emoney::admin.deposit.show', $data) - 상세보기 뷰 렌더링
 *
 * [컨트롤러 역할]
 * - 특정 충전 신청의 상세 정보 표시
 * - 사용자 정보 및 이머니 지갑 상태 조회
 * - 관련 거래 내역 표시
 * - 승인/거부 액션 버튼 제공
 *
 * [라우트 연결]
 * Route: GET /admin/auth/emoney/deposits/{id}
 * Name: admin.auth.emoney.deposits.show
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