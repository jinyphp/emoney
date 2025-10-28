<?php

namespace Jiny\Emoney\Tests\Feature\Admin\Withdrawal;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class WithdrawalManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 관리자 사용자 생성
        $this->adminUser = User::factory()->create([
            'isAdmin' => true,
            'utype' => 'admin',
        ]);

        // 일반 사용자 생성
        $this->regularUser = User::factory()->create([
            'uuid' => 'test-user-uuid-123',
        ]);

        // 테스트용 데이터베이스 테이블 생성
        $this->createTestTables();
    }

    protected function createTestTables(): void
    {
        // user_emoney_withdrawals 테이블 생성
        DB::statement('
            CREATE TABLE IF NOT EXISTS user_emoney_withdrawals (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_uuid VARCHAR(255) NOT NULL,
                amount DECIMAL(15,2) NOT NULL,
                fee DECIMAL(15,2) DEFAULT 0,
                currency VARCHAR(10) DEFAULT "KRW",
                method VARCHAR(50) DEFAULT "bank_transfer",
                bank_name VARCHAR(100),
                account_number VARCHAR(50),
                account_holder VARCHAR(100),
                status VARCHAR(20) DEFAULT "pending",
                checked BOOLEAN DEFAULT NULL,
                checked_at DATETIME DEFAULT NULL,
                checked_by INTEGER DEFAULT NULL,
                admin_memo TEXT DEFAULT NULL,
                user_memo TEXT DEFAULT NULL,
                reference_number VARCHAR(100) DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');

        // user_emoney 테이블 생성
        DB::statement('
            CREATE TABLE IF NOT EXISTS user_emoney (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_uuid VARCHAR(255) NOT NULL UNIQUE,
                balance DECIMAL(15,2) DEFAULT 0,
                total_deposit DECIMAL(15,2) DEFAULT 0,
                total_used DECIMAL(15,2) DEFAULT 0,
                total_withdrawn DECIMAL(15,2) DEFAULT 0,
                points DECIMAL(15,2) DEFAULT 0,
                currency VARCHAR(10) DEFAULT "KRW",
                status VARCHAR(20) DEFAULT "active",
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');

        // user_emoney_logs 테이블 생성
        DB::statement('
            CREATE TABLE IF NOT EXISTS user_emoney_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_uuid VARCHAR(255) NOT NULL,
                type VARCHAR(50) NOT NULL,
                amount DECIMAL(15,2) NOT NULL,
                balance_before DECIMAL(15,2) DEFAULT 0,
                balance_after DECIMAL(15,2) DEFAULT 0,
                description TEXT,
                reference_id INTEGER DEFAULT NULL,
                reference_type VARCHAR(50) DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');
    }

    /** @test */
    public function admin_can_view_withdrawal_list()
    {
        // Given: 출금 신청이 있을 때
        DB::table('user_emoney_withdrawals')->insert([
            'user_uuid' => $this->regularUser->uuid,
            'amount' => 30000,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: 관리자가 출금 목록을 조회할 때
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.auth.emoney.withdrawals.index'));

        // Then: 성공적으로 목록이 표시되어야 함
        $response->assertStatus(200);
        $response->assertViewIs('jiny-emoney::admin.withdrawal.index');
        $response->assertViewHas('withdrawals');
    }

    /** @test */
    public function admin_can_approve_pending_withdrawal()
    {
        // Given: 사용자에게 충분한 잔액이 있을 때
        DB::table('user_emoney')->insert([
            'user_uuid' => $this->regularUser->uuid,
            'balance' => 100000,
            'total_deposit' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // And: 대기중인 출금 신청이 있을 때
        $withdrawalId = DB::table('user_emoney_withdrawals')->insertGetId([
            'user_uuid' => $this->regularUser->uuid,
            'amount' => 30000,
            'fee' => 1000,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: 관리자가 출금을 승인할 때
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.auth.emoney.withdrawals.index.approve', $withdrawalId), [
                'admin_memo' => '정상 출금 처리'
            ]);

        // Then: 승인이 성공해야 함
        $response->assertRedirect(route('admin.auth.emoney.withdrawals.index'));
        $response->assertSessionHas('success');

        // And: 출금 신청 상태가 변경되어야 함
        $this->assertDatabaseHas('user_emoney_withdrawals', [
            'id' => $withdrawalId,
            'status' => 'approved',
            'checked' => '1',
            'checked_by' => $this->adminUser->id,
            'admin_memo' => '정상 출금 처리'
        ]);

        // And: 사용자 잔액이 감소해야 함 (출금액 + 수수료)
        $this->assertDatabaseHas('user_emoney', [
            'user_uuid' => $this->regularUser->uuid,
            'balance' => 69000, // 100000 - 30000 - 1000
            'total_withdrawn' => 31000, // 30000 + 1000
        ]);

        // And: 거래 로그가 기록되어야 함
        $this->assertDatabaseHas('user_emoney_logs', [
            'user_uuid' => $this->regularUser->uuid,
            'type' => 'withdrawal',
            'amount' => -31000, // 음수로 기록
            'balance_before' => 100000,
            'balance_after' => 69000,
            'reference_id' => $withdrawalId,
            'reference_type' => 'withdrawal'
        ]);
    }

    /** @test */
    public function admin_can_reject_pending_withdrawal()
    {
        // Given: 대기중인 출금 신청이 있을 때
        $withdrawalId = DB::table('user_emoney_withdrawals')->insertGetId([
            'user_uuid' => $this->regularUser->uuid,
            'amount' => 30000,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: 관리자가 출금을 거부할 때
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.auth.emoney.withdrawals.index.reject', $withdrawalId), [
                'admin_memo' => '잔액 부족'
            ]);

        // Then: 거부가 성공해야 함
        $response->assertRedirect(route('admin.auth.emoney.withdrawals.index'));
        $response->assertSessionHas('success');

        // And: 출금 신청 상태가 변경되어야 함
        $this->assertDatabaseHas('user_emoney_withdrawals', [
            'id' => $withdrawalId,
            'status' => 'rejected',
            'checked' => '0',
            'checked_by' => $this->adminUser->id,
            'admin_memo' => '잔액 부족'
        ]);
    }

    /** @test */
    public function admin_cannot_approve_withdrawal_with_insufficient_balance()
    {
        // Given: 사용자에게 잔액이 부족할 때
        DB::table('user_emoney')->insert([
            'user_uuid' => $this->regularUser->uuid,
            'balance' => 10000, // 부족한 잔액
            'total_deposit' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // And: 잔액보다 큰 출금 신청이 있을 때
        $withdrawalId = DB::table('user_emoney_withdrawals')->insertGetId([
            'user_uuid' => $this->regularUser->uuid,
            'amount' => 30000, // 잔액보다 큰 금액
            'fee' => 1000,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: 관리자가 출금을 승인하려고 할 때
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.auth.emoney.withdrawals.index.approve', $withdrawalId));

        // Then: 오류 메시지와 함께 리다이렉트되어야 함
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // And: 출금 신청 상태는 변경되지 않아야 함
        $this->assertDatabaseHas('user_emoney_withdrawals', [
            'id' => $withdrawalId,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function admin_cannot_approve_already_processed_withdrawal()
    {
        // Given: 이미 처리된 출금 신청이 있을 때
        $withdrawalId = DB::table('user_emoney_withdrawals')->insertGetId([
            'user_uuid' => $this->regularUser->uuid,
            'amount' => 30000,
            'status' => 'approved',
            'checked' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: 관리자가 다시 승인하려고 할 때
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.auth.emoney.withdrawals.index.approve', $withdrawalId));

        // Then: 오류 메시지와 함께 리다이렉트되어야 함
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function regular_user_cannot_access_admin_withdrawal_management()
    {
        // When: 일반 사용자가 관리자 페이지에 접근할 때
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.auth.emoney.withdrawals.index'));

        // Then: 접근이 거부되어야 함
        $response->assertStatus(302); // 리다이렉트
    }

    /** @test */
    public function withdrawal_approval_calculates_fee_correctly()
    {
        // Given: 사용자에게 충분한 잔액이 있을 때
        DB::table('user_emoney')->insert([
            'user_uuid' => $this->regularUser->uuid,
            'balance' => 100000,
            'total_deposit' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // And: 수수료가 포함된 출금 신청이 있을 때
        $withdrawalId = DB::table('user_emoney_withdrawals')->insertGetId([
            'user_uuid' => $this->regularUser->uuid,
            'amount' => 50000,
            'fee' => 2500, // 5% 수수료
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: 관리자가 출금을 승인할 때
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.auth.emoney.withdrawals.index.approve', $withdrawalId));

        // Then: 총 차감 금액(출금액 + 수수료)이 올바르게 계산되어야 함
        $this->assertDatabaseHas('user_emoney', [
            'user_uuid' => $this->regularUser->uuid,
            'balance' => 47500, // 100000 - 50000 - 2500
            'total_withdrawn' => 52500, // 50000 + 2500
        ]);
    }
}