<?php

namespace Jiny\Emoney\Http\Controllers\Bank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 은행 계좌 등록 처리
 */
class StoreController extends Controller
{
    use JWTAuthTrait;

    public function __invoke(Request $request)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        try {
            DB::beginTransaction();

            // 입력 데이터 검증
            $validatedData = $request->validate([
                'country' => 'required|string|max:10',
                'bank_name' => 'required|string|max:100',
                'bank_code' => 'required|string|max:20',
                'swift_code' => 'nullable|string|max:20',
                'account_number' => 'required|string|max:50',
                'account_holder' => 'required|string|max:100',
                'is_default' => 'nullable|boolean'
            ], [
                'country.required' => '국가를 선택해주세요.',
                'bank_name.required' => '은행을 선택해주세요.',
                'bank_code.required' => '은행 코드가 필요합니다.',
                'account_number.required' => '계좌번호를 입력해주세요.',
                'account_holder.required' => '예금주명을 입력해주세요.',
            ]);

            // 기본 계좌로 설정할 경우 기존 기본 계좌 해제
            if ($request->filled('is_default') && $request->is_default) {
                DB::table('user_emoney_bank')
                    ->where('user_id', $user->uuid)
                    ->update(['`default`' => '0']);
            }

            // 새 계좌 정보 저장
            $bankAccountData = [
                'user_id' => $user->uuid,
                'email' => $user->email,
                'type' => 'bank_account',
                'currency' => $validatedData['country'] === 'KR' ? 'KRW' : 'USD', // 기본 통화 설정
                'bank' => $validatedData['bank_name'],
                'swift' => $validatedData['swift_code'],
                'account' => $validatedData['account_number'],
                'owner' => $validatedData['account_holder'],
                'default' => $request->filled('is_default') && $request->is_default ? '1' : '0',
                'enable' => '1',
                'status' => 'active',
                'description' => json_encode([
                    'country' => $validatedData['country'],
                    'bank_code' => $validatedData['bank_code'],
                    'registered_at' => now()->toISOString()
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $bankAccountId = DB::table('user_emoney_bank')->insertGetId($bankAccountData);

            // 계좌 등록 로그 기록
            DB::table('user_emoney_logs')->insert([
                'user_uuid' => $user->uuid,
                'type' => 'bank_account_registered',
                'amount' => 0,
                'balance_before' => 0,
                'balance_after' => 0,
                'description' => "은행 계좌 등록: {$validatedData['bank_name']} ({$validatedData['account_number']})",
                'reference_id' => $bankAccountId,
                'reference_type' => 'bank_account',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('home.emoney.bank.index')
                ->with('success', '은행 계좌가 성공적으로 등록되었습니다.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Bank account registration failed', [
                'user_uuid' => $user->uuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', '계좌 등록 중 오류가 발생했습니다: ' . $e->getMessage())
                ->withInput();
        }
    }
}