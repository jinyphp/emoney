<?php

namespace Jiny\Emoney\Http\Controllers\Emoney\Deposit;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;
use Jiny\Auth\Facades\JwtAuth;

/**
 * 사용자 - 이머니 충전 신청 처리
 */
class StoreController extends Controller
{
    use JWTAuthTrait;

    /**
     * 이머니 충전 신청 처리
     */
    public function __invoke(Request $request)
    {
        // 디버깅을 위한 로그
        \Log::info('Deposit request started', [
            'method' => $request->method(),
            'url' => $request->url(),
            'headers' => $request->headers->all(),
            'data' => $request->all()
        ]);

        try {
            // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인 (파사드 사용)
            $user = JwtAuth::user($request);

            if (!$user) {
                \Log::warning('User authentication failed');
                return response()->json([
                    'success' => false,
                    'message' => '로그인이 필요합니다.'
                ], 401);
            }

            \Log::info('User authenticated', ['user_uuid' => $user->uuid]);

            // 입력 검증
            try {
                $validatedData = $request->validate([
                    'amount' => 'required|numeric|min:1000|max:1000000',
                    'bank_id' => 'required|exists:auth_banks,id',
                    'depositor_name' => 'required|string|max:100',
                    'deposit_date' => 'required|date',
                    'user_memo' => 'nullable|string|max:500',
                ], [
                    'amount.required' => '충전 금액을 입력해주세요.',
                    'amount.min' => '최소 충전 금액은 1,000원입니다.',
                    'amount.max' => '최대 충전 금액은 1,000,000원입니다.',
                    'bank_id.required' => '입금 은행을 선택해주세요.',
                    'bank_id.exists' => '선택한 은행이 유효하지 않습니다.',
                    'depositor_name.required' => '입금자명을 입력해주세요.',
                    'deposit_date.required' => '입금 날짜를 선택해주세요.',
                ]);

                \Log::info('Validation passed', $validatedData);
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::warning('Validation failed', ['errors' => $e->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => '입력 정보를 확인해주세요.',
                    'errors' => $e->errors()
                ], 422);
            }

            $userUuid = $user->uuid;
            $amount = $request->input('amount');

            DB::beginTransaction();

            // 선택된 은행 정보 조회
            $selectedBank = DB::table('auth_banks')
                ->where('id', $request->input('bank_id'))
                ->where('enable', true)
                ->first();

            \Log::info('Bank lookup', ['bank_id' => $request->input('bank_id'), 'found' => !!$selectedBank]);

            if (!$selectedBank) {
                throw new \Exception('선택한 은행 정보를 찾을 수 없습니다.');
            }

            // 충전 신청 생성
            $depositData = [
                'user_uuid' => $userUuid,
                'amount' => $amount,
                'currency' => 'KRW',
                'method' => 'bank_transfer',
                'bank_name' => $selectedBank->name,
                'bank_code' => $selectedBank->code,
                'depositor_name' => $request->input('depositor_name'),
                'deposit_date' => $request->input('deposit_date'),
                'status' => 'pending',
                'user_memo' => $request->input('user_memo'),
                'reference_number' => $this->generateReferenceNumber(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            \Log::info('Creating deposit record', $depositData);

            $depositId = DB::table('user_emoney_deposits')->insertGetId($depositData);

            \Log::info('Deposit created', ['deposit_id' => $depositId]);

            // 충전 로그 기록
            $logData = [
                'user_uuid' => $userUuid,
                'type' => 'deposit_request',
                'amount' => $amount,
                'balance_before' => 0, // 아직 잔액 변경 없음
                'balance_after' => 0,
                'description' => '충전 신청: ' . $selectedBank->name . ' / ' . $request->input('depositor_name'),
                'reference_id' => $depositId,
                'reference_type' => 'deposit',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('user_emoney_logs')->insert($logData);

            \Log::info('Log record created', $logData);

            DB::commit();

            $response = [
                'success' => true,
                'message' => '충전 신청이 완료되었습니다. 관리자 승인 후 적립됩니다.',
                'data' => [
                    'deposit_id' => $depositId,
                    'amount' => number_format($amount),
                    'bank' => $selectedBank->name,
                    'status' => 'pending'
                ]
            ];

            \Log::info('Deposit request successful', $response);

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Deposit request failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '충전 신청 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 참조번호 생성
     */
    private function generateReferenceNumber(): string
    {
        return 'DEP' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}