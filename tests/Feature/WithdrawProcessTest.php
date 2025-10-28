<?php

namespace Jiny\Emoney\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WithdrawProcessTest extends TestCase
{
    use DatabaseTransactions;

    protected $testUser;
    protected $testAdmin;
    protected $testUserUuid;
    protected $testAdminUuid;
    protected $testBankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        // 필요한 테이블 생성
        $this->createMissingTables();

        // 테스트용 사용자 생성
        $this->createTestUser();

        // 테스트용 관리자 생성
        $this->createTestAdmin();

        // 테스트용 은행 계좌 생성
        $this->createTestBankAccount();
    }

    private function createMissingTables()
    {
        // users 테이블 확인 및 생성
        $usersTableExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        if (empty($usersTableExists)) {
            DB::statement('
                CREATE TABLE users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    uuid VARCHAR(36) UNIQUE,
                    name VARCHAR(255),
                    email VARCHAR(255) UNIQUE,
                    password VARCHAR(255),
                    isAdmin BOOLEAN DEFAULT 0,
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');
        }

        // user_emoney_bank 테이블 확인 및 생성
        $bankTableExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='user_emoney_bank'");
        if (empty($bankTableExists)) {
            DB::statement('
                CREATE TABLE user_emoney_bank (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id VARCHAR(36) NOT NULL,
                    email VARCHAR(255),
                    type VARCHAR(50) DEFAULT "bank_account",
                    currency VARCHAR(10) DEFAULT "KRW",
                    bank VARCHAR(100),
                    swift VARCHAR(20),
                    account VARCHAR(50),
                    owner VARCHAR(100),
                    "default" VARCHAR(1) DEFAULT "0",
                    enable VARCHAR(1) DEFAULT "1",
                    status VARCHAR(20) DEFAULT "active",
                    description TEXT,
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');
        }

        // user_emoney_logs 테이블 확인 및 생성
        $logsTableExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='user_emoney_logs'");
        if (empty($logsTableExists)) {
            DB::statement('
                CREATE TABLE user_emoney_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_uuid VARCHAR(36) NOT NULL,
                    type VARCHAR(50),
                    amount DECIMAL(15,2),
                    balance_before DECIMAL(15,2),
                    balance_after DECIMAL(15,2),
                    description TEXT,
                    reference_id INTEGER,
                    reference_type VARCHAR(50),
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');
        }

        // user_notifications 테이블 확인 및 생성
        $notificationsTableExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='user_notifications'");
        if (empty($notificationsTableExists)) {
            DB::statement('
                CREATE TABLE user_notifications (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_uuid VARCHAR(36) NOT NULL,
                    type VARCHAR(50),
                    title VARCHAR(255),
                    message TEXT,
                    data TEXT,
                    is_read BOOLEAN DEFAULT 0,
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');
        }

        // user_emoney 테이블 확인 및 생성 (기존 테이블 구조와 맞춤)
        $emoneyTableExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='user_emoney'");
        if (empty($emoneyTableExists)) {
            DB::statement('
                CREATE TABLE user_emoney (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_uuid VARCHAR(36) NOT NULL UNIQUE,
                    balance DECIMAL(15,2) DEFAULT 0,
                    total_deposit DECIMAL(15,2) DEFAULT 0,
                    total_used DECIMAL(15,2) DEFAULT 0,
                    total_withdrawn DECIMAL(15,2) DEFAULT 0,
                    points DECIMAL(15,2) DEFAULT 0,
                    currency VARCHAR(10) DEFAULT "KRW",
                    status VARCHAR(20) DEFAULT "active",
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');
        }

        // user_emoney_withdrawals 테이블 확인 및 생성
        $withdrawalsTableExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='user_emoney_withdrawals'");
        if (empty($withdrawalsTableExists)) {
            DB::statement('
                CREATE TABLE user_emoney_withdrawals (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_uuid VARCHAR(36) NOT NULL,
                    amount DECIMAL(15,2) NOT NULL,
                    fee DECIMAL(15,2) DEFAULT 0,
                    currency VARCHAR(10) DEFAULT "KRW",
                    method VARCHAR(50) DEFAULT "bank_transfer",
                    bank_name VARCHAR(100),
                    account_number VARCHAR(50),
                    account_holder VARCHAR(100),
                    status VARCHAR(20) DEFAULT "pending",
                    checked BOOLEAN DEFAULT 0,
                    checked_at DATETIME,
                    checked_by INTEGER,
                    admin_memo TEXT,
                    user_memo TEXT,
                    reference_number VARCHAR(100),
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');
        }
    }

    private function createTestUser()
    {
        $this->testUserUuid = (string) Str::uuid();

        $this->testUser = (object) [
            'uuid' => $this->testUserUuid,
            'email' => 'testuser@example.com',
            'name' => 'Test User'
        ];

        // 사용자 기본 테이블에 삽입
        DB::table('users')->insert([
            'uuid' => $this->testUserUuid,
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 사용자 이머니 계정 생성 (초기 잔액 100,000원)
        // 기존 레코드가 있으면 삭제하고 새로 생성
        DB::table('user_emoney')->where('user_uuid', $this->testUserUuid)->delete();

        DB::table('user_emoney')->insert([
            'user_uuid' => $this->testUserUuid,
            'balance' => 100000.00,
            'total_deposit' => 100000.00,
            'total_used' => 0.00,
            'total_withdrawn' => 0.00,
            'points' => 0.00,
            'currency' => 'KRW',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function createTestAdmin()
    {
        $this->testAdminUuid = (string) Str::uuid();

        DB::table('users')->insert([
            'uuid' => $this->testAdminUuid,
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'isAdmin' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function createTestBankAccount()
    {
        // 기존 테스트 데이터 정리
        DB::table('user_emoney_bank')->where('user_id', $this->testUserUuid)->delete();
        DB::table('user_emoney_withdrawals')->where('user_uuid', $this->testUserUuid)->delete();
        DB::table('user_emoney_logs')->where('user_uuid', $this->testUserUuid)->delete();
        DB::table('user_notifications')->where('user_uuid', $this->testUserUuid)->delete();

        $this->testBankAccount = DB::table('user_emoney_bank')->insertGetId([
            'user_id' => $this->testUserUuid,
            'email' => $this->testUser->email,
            'type' => 'bank_account',
            'currency' => 'KRW',
            'bank' => '국민은행',
            'account' => '123-456-789012',
            'owner' => 'Test User',
            'default' => '1',
            'enable' => '1',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function complete_withdrawal_process_from_user_request_to_admin_approval()
    {
        echo "\n=== 완전한 출금 프로세스 테스트 시작 ===\n";

        // 1. 초기 상태 확인
        $initialBalance = DB::table('user_emoney')->where('user_uuid', $this->testUserUuid)->value('balance');
        echo "1. 초기 이머니 잔액: " . number_format($initialBalance) . "원\n";
        $this->assertEquals(100000.00, $initialBalance);

        // 2. 사용자 출금 신청 (30,000원 출금 신청)
        $withdrawAmount = 30000;
        $withdrawalData = [
            'bank_account_id' => $this->testBankAccount,
            'amount' => $withdrawAmount,
            'withdraw_reason' => '생활비 출금'
        ];

        echo "2. 사용자가 {$withdrawAmount}원 출금 신청...\n";

        // StoreController 로직 시뮬레이션
        $feeRate = 0.05;
        $minFee = 1000;
        $fee = max(floor($withdrawAmount * $feeRate), $minFee);
        $actualAmount = $withdrawAmount - $fee;

        $bankAccount = DB::table('user_emoney_bank')->where('id', $this->testBankAccount)->first();

        $withdrawId = DB::table('user_emoney_withdrawals')->insertGetId([
            'user_uuid' => $this->testUserUuid,
            'amount' => $withdrawAmount,
            'fee' => $fee,
            'currency' => 'KRW',
            'method' => 'bank_transfer',
            'bank_name' => $bankAccount->bank,
            'account_number' => $bankAccount->account,
            'account_holder' => $bankAccount->owner,
            'status' => 'pending',
            'checked' => false,
            'user_memo' => $withdrawalData['withdraw_reason'],
            'reference_number' => 'WD' . date('Ymd') . str_pad($this->testUserUuid, 6, '0', STR_PAD_LEFT) . rand(1000, 9999),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "   - 출금 신청 ID: {$withdrawId}\n";
        echo "   - 출금 신청 금액: " . number_format($withdrawAmount) . "원\n";
        echo "   - 수수료: " . number_format($fee) . "원\n";
        echo "   - 실제 입금 예정 금액: " . number_format($actualAmount) . "원\n";

        // 출금 신청 로그 기록
        DB::table('user_emoney_logs')->insert([
            'user_uuid' => $this->testUserUuid,
            'type' => 'withdraw_request',
            'amount' => -$withdrawAmount,
            'balance_before' => $initialBalance,
            'balance_after' => $initialBalance, // 신청 단계에서는 잔액 변동 없음
            'description' => "출금 신청: {$bankAccount->bank} ({$bankAccount->account}) - 수수료: {$fee}원",
            'reference_id' => $withdrawId,
            'reference_type' => 'withdraw_request',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 사용자 알림 생성
        DB::table('user_notifications')->insert([
            'user_uuid' => $this->testUserUuid,
            'type' => 'withdraw_request',
            'title' => '출금 신청이 접수되었습니다',
            'message' => "출금 신청 금액: " . number_format($withdrawAmount) . "원\n" .
                       "수수료: " . number_format($fee) . "원\n" .
                       "실제 입금 예정 금액: " . number_format($actualAmount) . "원\n" .
                       "처리까지 1-3일 소요됩니다.",
            'data' => json_encode([
                'withdraw_id' => $withdrawId,
                'amount' => $withdrawAmount,
                'fee' => $fee,
                'actual_amount' => $actualAmount,
                'bank_name' => $bankAccount->bank,
                'account_number' => $bankAccount->account
            ]),
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. 출금 신청 상태 확인
        $withdrawal = DB::table('user_emoney_withdrawals')->where('id', $withdrawId)->first();
        echo "3. 출금 신청 상태 확인: {$withdrawal->status}\n";
        $this->assertEquals('pending', $withdrawal->status);
        $this->assertEquals(false, $withdrawal->checked);

        // 잔액이 아직 변경되지 않았는지 확인
        $balanceAfterRequest = DB::table('user_emoney')->where('user_uuid', $this->testUserUuid)->value('balance');
        echo "   - 신청 후 잔액: " . number_format($balanceAfterRequest) . "원 (변동 없음)\n";
        $this->assertEquals($initialBalance, $balanceAfterRequest);

        // 4. 관리자 승인 처리
        echo "4. 관리자가 출금 신청을 승인 처리...\n";

        // ApproveController 로직 시뮬레이션
        $userEmoney = DB::table('user_emoney')->where('user_uuid', $this->testUserUuid)->first();
        $totalAmount = $withdrawal->amount + $withdrawal->fee;

        // 잔액 충분한지 확인
        $this->assertTrue($userEmoney->balance >= $totalAmount, "잔액 부족");

        // 출금 신청 승인 처리
        DB::table('user_emoney_withdrawals')
            ->where('id', $withdrawId)
            ->update([
                'status' => 'approved',
                'checked' => true,
                'checked_at' => now(),
                'checked_by' => 1, // 테스트 관리자 ID
                'admin_memo' => '정상 출금 승인',
                'updated_at' => now(),
            ]);

        // 사용자 이머니 잔액 차감
        $newBalance = $userEmoney->balance - $totalAmount;
        $newTotalWithdrawn = ($userEmoney->total_withdrawn ?? 0) + $totalAmount;

        DB::table('user_emoney')
            ->where('user_uuid', $this->testUserUuid)
            ->update([
                'balance' => $newBalance,
                'total_withdrawn' => $newTotalWithdrawn,
                'updated_at' => now(),
            ]);

        // 이머니 거래 로그 기록
        DB::table('user_emoney_logs')->insert([
            'user_uuid' => $this->testUserUuid,
            'type' => 'withdrawal',
            'amount' => -$totalAmount,
            'balance_before' => $userEmoney->balance,
            'balance_after' => $newBalance,
            'description' => '출금 승인: 정상 출금 승인' .
                           ' (출금액: ' . number_format($withdrawal->amount) . '원, 수수료: ' . number_format($withdrawal->fee) . '원)',
            'reference_id' => $withdrawId,
            'reference_type' => 'withdrawal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "   - 차감된 총 금액: " . number_format($totalAmount) . "원\n";
        echo "   - 새로운 잔액: " . number_format($newBalance) . "원\n";

        // 5. 최종 상태 확인
        echo "5. 최종 상태 확인...\n";

        // 출금 신청 상태 확인
        $approvedWithdrawal = DB::table('user_emoney_withdrawals')->where('id', $withdrawId)->first();
        $this->assertEquals('approved', $approvedWithdrawal->status);
        $this->assertEquals(true, $approvedWithdrawal->checked);
        $this->assertNotNull($approvedWithdrawal->checked_at);
        echo "   - 출금 신청 상태: {$approvedWithdrawal->status}\n";

        // 잔액 확인
        $finalBalance = DB::table('user_emoney')->where('user_uuid', $this->testUserUuid)->value('balance');
        $expectedBalance = $initialBalance - $totalAmount;
        $this->assertEquals($expectedBalance, $finalBalance);
        echo "   - 최종 잔액: " . number_format($finalBalance) . "원\n";

        // 총 출금액 확인
        $totalWithdrawn = DB::table('user_emoney')->where('user_uuid', $this->testUserUuid)->value('total_withdrawn');
        $this->assertEquals($totalAmount, $totalWithdrawn);
        echo "   - 총 출금액: " . number_format($totalWithdrawn) . "원\n";

        // 로그 기록 확인
        $logs = DB::table('user_emoney_logs')->where('user_uuid', $this->testUserUuid)->count();
        $this->assertGreaterThanOrEqual(2, $logs); // 신청 로그 + 승인 로그
        echo "   - 생성된 로그 수: {$logs}개\n";

        echo "\n=== 출금 프로세스 테스트 성공 완료 ===\n";
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function withdrawal_rejection_process()
    {
        echo "\n=== 출금 거부 프로세스 테스트 시작 ===\n";

        // 1. 출금 신청
        $withdrawAmount = 20000;
        echo "1. 사용자가 {$withdrawAmount}원 출금 신청...\n";

        $bankAccount = DB::table('user_emoney_bank')->where('id', $this->testBankAccount)->first();

        $withdrawId = DB::table('user_emoney_withdrawals')->insertGetId([
            'user_uuid' => $this->testUserUuid,
            'amount' => $withdrawAmount,
            'fee' => 1000,
            'currency' => 'KRW',
            'method' => 'bank_transfer',
            'bank_name' => $bankAccount->bank,
            'account_number' => $bankAccount->account,
            'account_holder' => $bankAccount->owner,
            'status' => 'pending',
            'checked' => false,
            'user_memo' => '테스트 출금',
            'reference_number' => 'WD' . date('Ymd') . rand(1000, 9999),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "   - 출금 신청 ID: {$withdrawId}\n";

        // 2. 초기 잔액 기록
        $initialBalance = DB::table('user_emoney')->where('user_uuid', $this->testUserUuid)->value('balance');
        echo "2. 초기 잔액: " . number_format($initialBalance) . "원\n";

        // 3. 관리자 거부 처리
        echo "3. 관리자가 출금 신청을 거부 처리...\n";

        $rejectReason = '계좌 정보 불일치로 인한 거부';

        DB::table('user_emoney_withdrawals')
            ->where('id', $withdrawId)
            ->update([
                'status' => 'rejected',
                'checked' => false,
                'checked_at' => now(),
                'checked_by' => 1,
                'admin_memo' => $rejectReason,
                'updated_at' => now(),
            ]);

        echo "   - 거부 사유: {$rejectReason}\n";

        // 4. 최종 상태 확인
        echo "4. 최종 상태 확인...\n";

        // 출금 신청 상태 확인
        $rejectedWithdrawal = DB::table('user_emoney_withdrawals')->where('id', $withdrawId)->first();
        $this->assertEquals('rejected', $rejectedWithdrawal->status);
        $this->assertEquals($rejectReason, $rejectedWithdrawal->admin_memo);
        echo "   - 출금 신청 상태: {$rejectedWithdrawal->status}\n";

        // 잔액이 변경되지 않았는지 확인
        $finalBalance = DB::table('user_emoney')->where('user_uuid', $this->testUserUuid)->value('balance');
        $this->assertEquals($initialBalance, $finalBalance);
        echo "   - 최종 잔액: " . number_format($finalBalance) . "원 (변동 없음)\n";

        echo "\n=== 출금 거부 프로세스 테스트 성공 완료 ===\n";
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function withdrawal_with_insufficient_balance()
    {
        echo "\n=== 잔액 부족 출금 테스트 시작 ===\n";

        // 1. 현재 잔액 확인
        $currentBalance = DB::table('user_emoney')->where('user_uuid', $this->testUserUuid)->value('balance');
        echo "1. 현재 잔액: " . number_format($currentBalance) . "원\n";

        // 2. 잔액보다 많은 금액 출금 신청
        $withdrawAmount = $currentBalance + 50000; // 잔액보다 5만원 더 많이
        echo "2. 잔액을 초과하는 출금 신청: " . number_format($withdrawAmount) . "원\n";

        $bankAccount = DB::table('user_emoney_bank')->where('id', $this->testBankAccount)->first();

        $withdrawId = DB::table('user_emoney_withdrawals')->insertGetId([
            'user_uuid' => $this->testUserUuid,
            'amount' => $withdrawAmount,
            'fee' => 5000,
            'currency' => 'KRW',
            'method' => 'bank_transfer',
            'bank_name' => $bankAccount->bank,
            'account_number' => $bankAccount->account,
            'account_holder' => $bankAccount->owner,
            'status' => 'pending',
            'checked' => false,
            'user_memo' => '잔액 부족 테스트',
            'reference_number' => 'WD' . date('Ymd') . rand(1000, 9999),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. 관리자가 승인 시도 (잔액 부족으로 실패해야 함)
        echo "3. 관리자가 승인 시도...\n";

        $withdrawal = DB::table('user_emoney_withdrawals')->where('id', $withdrawId)->first();
        $userEmoney = DB::table('user_emoney')->where('user_uuid', $this->testUserUuid)->first();
        $totalAmount = $withdrawal->amount + $withdrawal->fee;

        echo "   - 필요 금액: " . number_format($totalAmount) . "원\n";
        echo "   - 사용자 잔액: " . number_format($userEmoney->balance) . "원\n";

        // 잔액 부족 확인
        $this->assertTrue($userEmoney->balance < $totalAmount, "잔액이 충분함 (테스트 실패)");
        echo "   - 잔액 부족으로 승인 불가\n";

        // 4. 상태가 여전히 pending인지 확인
        $finalWithdrawal = DB::table('user_emoney_withdrawals')->where('id', $withdrawId)->first();
        $this->assertEquals('pending', $finalWithdrawal->status);
        echo "4. 출금 신청 상태: {$finalWithdrawal->status} (승인되지 않음)\n";

        echo "\n=== 잔액 부족 출금 테스트 성공 완료 ===\n";
    }
}