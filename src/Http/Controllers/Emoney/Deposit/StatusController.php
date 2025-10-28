<?php

namespace Jiny\Emoney\Http\Controllers\Emoney\Deposit;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 이머니 충전 상태 조회
 */
class StatusController extends Controller
{
    use JWTAuthTrait;

    /**
     * 충전 상태 조회 (AJAX)
     */
    public function __invoke(Request $request, int $depositId)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '로그인이 필요합니다.'
            ], 401);
        }

        $userUuid = $user->uuid;

        // 충전 신청 정보 조회 (본인 신청만)
        $deposit = DB::table('user_emoney_deposits')
            ->where('id', $depositId)
            ->where('user_uuid', $userUuid)
            ->first();

        if (!$deposit) {
            return response()->json([
                'success' => false,
                'message' => '충전 신청 정보를 찾을 수 없습니다.'
            ], 404);
        }

        // 상태별 메시지 및 추가 정보 설정
        $statusInfo = $this->getStatusInfo($deposit);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $deposit->id,
                'amount' => $deposit->amount,
                'formatted_amount' => number_format($deposit->amount),
                'currency' => $deposit->currency,
                'bank_name' => $deposit->bank_name,
                'depositor_name' => $deposit->depositor_name,
                'deposit_date' => $deposit->deposit_date,
                'status' => $deposit->status,
                'status_text' => $statusInfo['text'],
                'status_color' => $statusInfo['color'],
                'status_icon' => $statusInfo['icon'],
                'can_cancel' => $statusInfo['can_cancel'],
                'admin_memo' => $deposit->admin_memo,
                'reference_number' => $deposit->reference_number,
                'created_at' => $deposit->created_at,
                'checked_at' => $deposit->checked_at,
                'progress_percentage' => $statusInfo['progress'],
            ]
        ]);
    }

    /**
     * 상태에 따른 정보 반환
     */
    private function getStatusInfo($deposit): array
    {
        switch ($deposit->status) {
            case 'pending':
                return [
                    'text' => '승인 대기',
                    'color' => 'warning',
                    'icon' => 'clock',
                    'can_cancel' => true,
                    'progress' => 25,
                ];

            case 'approved':
                return [
                    'text' => '승인 완료',
                    'color' => 'success',
                    'icon' => 'check-circle',
                    'can_cancel' => false,
                    'progress' => 100,
                ];

            case 'rejected':
                return [
                    'text' => '승인 거부',
                    'color' => 'danger',
                    'icon' => 'x-circle',
                    'can_cancel' => false,
                    'progress' => 100,
                ];

            case 'cancelled':
                return [
                    'text' => '취소 요청',
                    'color' => 'secondary',
                    'icon' => 'dash-circle',
                    'can_cancel' => false,
                    'progress' => 50,
                ];

            case 'refunded':
                return [
                    'text' => '환불 완료',
                    'color' => 'info',
                    'icon' => 'arrow-left-circle',
                    'can_cancel' => false,
                    'progress' => 100,
                ];

            default:
                return [
                    'text' => '알 수 없음',
                    'color' => 'secondary',
                    'icon' => 'question-circle',
                    'can_cancel' => false,
                    'progress' => 0,
                ];
        }
    }
}