<?php

namespace Jiny\Emoney\Http\Controllers\Emoney\Withdraw;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 이머니 출금 신청 처리
 *
 * [메소드 호출 관계 트리]
 * StoreController
 * └── __invoke(Request $request)
 *     ├── getAuthenticatedUser($request) - JWT 다중 인증 방식으로 사용자 확인
 *     ├── DB::beginTransaction() - 트랜잭션 시작
 *     ├── $request->validate() - 입력값 유효성 검사
 *     │   ├── bank_account_id: 필수, 정수
 *     │   ├── amount: 필수, 숫자, 최소 5000원
 *     │   └── withdraw_reason: 선택, 문자열, 최대 500자
 *     ├── DB::table('user_emoney')->where('user_uuid', $userUuid)->first() - 사용자 이머니 잔액 확인
 *     ├── DB::table('user_emoney_bank') - 선택된 은행계좌 검증
 *     │   ├── ->where('id', $bankAccountId) - 계좌 ID 확인
 *     │   ├── ->where('user_id', $userUuid) - 본인 계좌 확인
 *     │   └── ->where('enable', '1') - 활성화된 계좌만
 *     ├── 출금 수수료 계산 (5% 또는 최소 1000원)
 *     ├── DB::table('user_emoney_withdrawals')->insertGetId() - 출금 신청 기록
 *     ├── DB::table('user_emoney_logs')->insert() - 출금 신청 로그 기록
 *     ├── DB::table('user_notifications')->insert() - 사용자 알림 생성
 *     ├── DB::commit() - 트랜잭션 커밋
 *     └── redirect()->route('home.emoney.withdraw') - 성공 리다이렉트
 *
 * [컨트롤러 역할]
 * - 사용자의 이머니 출금 신청 처리
 * - 출금 가능 금액 검증 (잔액 확인)
 * - 등록된 은행계좌 검증
 * - 출금 수수료 계산 및 적용
 * - 출금 신청 데이터 저장 (pending 상태)
 * - 출금 신청 로그 기록
 * - 사용자 알림 발송
 *
 * [유효성 검사 규칙]
 * - bank_account_id: 필수, 등록된 은행계좌 ID
 * - amount: 필수, 숫자, 최소 5,000원
 * - withdraw_reason: 선택, 출금 사유 (최대 500자)
 *
 * [비즈니스 로직]
 * 1. 사용자 인증 및 이머니 잔액 확인
 * 2. 선택된 은행계좌 유효성 검증
 * 3. 출금 수수료 계산 (5% 또는 최소 1,000원)
 * 4. 출금 신청 레코드 생성 (pending 상태)
 * 5. 출금 신청 로그 기록
 * 6. 사용자 알림 발송
 * 7. 관리자 승인 대기 상태로 설정
 *
 * [수수료 계산]
 * - 기본 수수료율: 5%
 * - 최소 수수료: 1,000원
 * - 실제 입금액 = 출금신청액 - 수수료
 *
 * [출금 프로세스]
 * 1. 출금 신청 (pending 상태)
 * 2. 관리자 승인/거부 처리
 * 3. 승인 시 실제 이머니 잔액 차감
 * 4. 은행 송금 처리
 * 5. 완료 알림 발송
 *
 * [참조번호 생성]
 * - 형식: WD + YYYYMMDD + 사용자UUID(6자리) + 랜덤(4자리)
 * - 예: WD20241028000123456789
 *
 * [보안 고려사항]
 * - 본인 소유 은행계좌만 사용 가능
 * - 트랜잭션으로 데이터 일관성 보장
 * - 출금 신청 단계에서는 이머니 잔액 변동 없음
 * - 관리자 승인 후 실제 차감 처리
 *
 * [라우트 연결]
 * Route: POST /emoney/withdraw
 * Name: home.emoney.withdraw.store
 *
 * [관련 컨트롤러]
 * - IndexController: 출금 페이지 표시
 * - HistoryController: 출금 내역 조회
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
                'bank_account_id' => 'required|integer',
                'amount' => 'required|numeric|min:5000',
                'withdraw_reason' => 'nullable|string|max:500'
            ], [
                'bank_account_id.required' => '출금 계좌를 선택해주세요.',
                'amount.required' => '출금 금액을 입력해주세요.',
                'amount.min' => '최소 출금 금액은 5,000원입니다.',
                'withdraw_reason.max' => '출금 사유는 500자 이내로 입력해주세요.'
            ]);

            $userUuid = $user->uuid;
            $amount = $validatedData['amount'];

            // 사용자 이머니 잔액 확인
            $userEmoney = DB::table('user_emoney')->where('user_uuid', $userUuid)->first();
            if (!$userEmoney || $userEmoney->balance < $amount) {
                throw new \Exception('잔액이 부족합니다.');
            }

            // 선택한 은행 계좌 확인
            $bankAccount = DB::table('user_emoney_bank')
                ->where('id', $validatedData['bank_account_id'])
                ->where('user_id', $userUuid)
                ->where('enable', '1')
                ->first();

            if (!$bankAccount) {
                throw new \Exception('유효하지 않은 계좌입니다.');
            }

            // 출금 수수료 계산 (5% 또는 최소 1,000원)
            $feeRate = 0.05;
            $minFee = 1000;
            $fee = max(floor($amount * $feeRate), $minFee);
            $actualAmount = $amount - $fee;

            // 출금 신청 데이터 생성 (user_emoney_withdrawals 테이블 구조에 맞춤)
            $withdrawData = [
                'user_uuid' => $userUuid,
                'amount' => $amount,
                'fee' => $fee,
                'currency' => $bankAccount->currency ?? 'KRW',
                'method' => 'bank_transfer',
                'bank_name' => $bankAccount->bank,
                'account_number' => $bankAccount->account,
                'account_holder' => $bankAccount->owner,
                'status' => 'pending',
                'checked' => false,
                'user_memo' => $validatedData['withdraw_reason'] ?? null,
                'reference_number' => 'WD' . date('Ymd') . str_pad($userUuid, 6, '0', STR_PAD_LEFT) . rand(1000, 9999),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $withdrawId = DB::table('user_emoney_withdrawals')->insertGetId($withdrawData);

            // 출금 신청 로그 기록
            DB::table('user_emoney_logs')->insert([
                'user_uuid' => $userUuid,
                'type' => 'withdraw_request',
                'amount' => -$amount, // 출금은 음수로 기록
                'balance_before' => $userEmoney->balance,
                'balance_after' => $userEmoney->balance, // 신청 단계에서는 잔액 변동 없음
                'description' => "출금 신청: {$bankAccount->bank} ({$bankAccount->account}) - 수수료: {$fee}원",
                'reference_id' => $withdrawId,
                'reference_type' => 'withdraw_request',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 사용자 알림 생성
            DB::table('user_notifications')->insert([
                'user_uuid' => $userUuid,
                'email' => $user->email,
                'name' => $user->name ?? 'User',
                'type' => 'withdraw_request',
                'title' => '출금 신청이 접수되었습니다',
                'message' => "출금 신청 금액: " . number_format($amount) . "원\n" .
                           "수수료: " . number_format($fee) . "원\n" .
                           "실제 입금 예정 금액: " . number_format($actualAmount) . "원\n" .
                           "처리까지 1-3일 소요됩니다.",
                'data' => json_encode([
                    'withdraw_id' => $withdrawId,
                    'amount' => $amount,
                    'fee' => $fee,
                    'actual_amount' => $actualAmount,
                    'bank_name' => $bankAccount->bank,
                    'account_number' => $bankAccount->account
                ]),
                'status' => 'unread',
                'priority' => 'normal',
                'enable' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('home.emoney.withdraw')
                ->with('success', '출금 신청이 성공적으로 접수되었습니다. 관리자 승인 후 처리됩니다.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Withdraw request failed', [
                'user_uuid' => $user->uuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', '출금 신청 중 오류가 발생했습니다: ' . $e->getMessage())
                ->withInput();
        }
    }
}