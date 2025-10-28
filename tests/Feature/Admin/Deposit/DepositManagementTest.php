<?php

namespace Jiny\Emoney\Tests\Feature\Admin\Deposit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DepositManagementTest extends TestCase
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
    }


    /** @test */
    public function admin_can_view_deposit_list()
    {
        // Given: 충전 신청이 있을 때
        DB::table('user_emoney_deposits')->insert([
            'user_uuid' => $this->regularUser->uuid,
            'amount' => 50000,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: 관리자가 충전 목록을 조회할 때
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.auth.emoney.deposits.index'));

        // Then: 성공적으로 목록이 표시되어야 함
        $response->assertStatus(200);
        $response->assertViewIs('jiny-emoney::admin.deposit.index');
        $response->assertViewHas('deposits');
    }

    /** @test */
    public function admin_can_approve_pending_deposit()
    {
        // Given: 대기중인 충전 신청이 있을 때
        $depositId = DB::table('user_emoney_deposits')->insertGetId([
            'user_uuid' => $this->regularUser->uuid,
            'amount' => 50000,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 사용자 이머니 지갑 생성
        DB::table('user_emoney')->insert([
            'user_uuid' => $this->regularUser->uuid,
            'balance' => 10000,
            'total_deposit' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: 관리자가 충전을 승인할 때
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.auth.emoney.deposits.approve', $depositId), [
                'admin_memo' => '정상 입금 확인됨'
            ]);

        // Then: 승인이 성공해야 함
        $response->assertRedirect(route('admin.auth.emoney.deposits.index'));
        $response->assertSessionHas('success');

        // And: 충전 신청 상태가 변경되어야 함
        $this->assertDatabaseHas('user_emoney_deposits', [
            'id' => $depositId,
            'status' => 'approved',
            'checked' => '1',
            'checked_by' => $this->adminUser->id,
            'admin_memo' => '정상 입금 확인됨'
        ]);

        // And: 사용자 잔액이 증가해야 함
        $this->assertDatabaseHas('user_emoney', [
            'user_uuid' => $this->regularUser->uuid,
            'balance' => 60000, // 10000 + 50000
            'total_deposit' => 60000, // 10000 + 50000
        ]);

        // And: 거래 로그가 기록되어야 함
        $this->assertDatabaseHas('user_emoney_logs', [
            'user_uuid' => $this->regularUser->uuid,
            'type' => 'deposit',
            'amount' => 50000,
            'balance_before' => 10000,
            'balance_after' => 60000,
            'reference_id' => $depositId,
            'reference_type' => 'deposit'
        ]);
    }

    /** @test */
    public function admin_can_reject_pending_deposit()
    {
        // Given: 대기중인 충전 신청이 있을 때
        $depositId = DB::table('user_emoney_deposits')->insertGetId([
            'user_uuid' => $this->regularUser->uuid,
            'amount' => 50000,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: 관리자가 충전을 거부할 때
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.auth.emoney.deposits.reject', $depositId), [
                'admin_memo' => '입금 확인 불가'
            ]);

        // Then: 거부가 성공해야 함
        $response->assertRedirect(route('admin.auth.emoney.deposits.index'));
        $response->assertSessionHas('success');

        // And: 충전 신청 상태가 변경되어야 함
        $this->assertDatabaseHas('user_emoney_deposits', [
            'id' => $depositId,
            'status' => 'rejected',
            'checked' => '0',
            'checked_by' => $this->adminUser->id,
            'admin_memo' => '입금 확인 불가'
        ]);
    }

    /** @test */
    public function admin_cannot_approve_already_processed_deposit()
    {
        // Given: 이미 처리된 충전 신청이 있을 때
        $depositId = DB::table('user_emoney_deposits')->insertGetId([
            'user_uuid' => $this->regularUser->uuid,
            'amount' => 50000,
            'status' => 'approved',
            'checked' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: 관리자가 다시 승인하려고 할 때
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.auth.emoney.deposits.approve', $depositId));

        // Then: 오류 메시지와 함께 리다이렉트되어야 함
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function regular_user_cannot_access_admin_deposit_management()
    {
        // When: 일반 사용자가 관리자 페이지에 접근할 때
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.auth.emoney.deposits.index'));

        // Then: 접근이 거부되어야 함
        $response->assertStatus(302); // 리다이렉트
    }

    /** @test */
    public function approval_creates_emoney_wallet_if_not_exists()
    {
        // Given: 이머니 지갑이 없는 사용자의 충전 신청이 있을 때
        $depositId = DB::table('user_emoney_deposits')->insertGetId([
            'user_uuid' => $this->regularUser->uuid,
            'amount' => 50000,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 이머니 지갑이 없음을 확인
        $this->assertDatabaseMissing('user_emoney', [
            'user_uuid' => $this->regularUser->uuid,
        ]);

        // When: 관리자가 충전을 승인할 때
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.auth.emoney.deposits.approve', $depositId));

        // Then: 새로운 이머니 지갑이 생성되어야 함
        $this->assertDatabaseHas('user_emoney', [
            'user_uuid' => $this->regularUser->uuid,
            'balance' => 50000,
            'total_deposit' => 50000,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function admin_can_delete_pending_deposit()
    {
        // Given: 대기중인 충전 신청이 있을 때
        $depositId = DB::table('user_emoney_deposits')->insertGetId([
            'user_uuid' => $this->regularUser->uuid,
            'amount' => 30000,
            'status' => 'pending',
            'method' => 'bank_transfer',
            'depositor_name' => '테스트입금자',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 관련 로그 생성
        DB::table('user_emoney_logs')->insert([
            'user_uuid' => $this->regularUser->uuid,
            'type' => 'deposit_pending',
            'amount' => 30000,
            'reference_id' => $depositId,
            'reference_type' => 'deposit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: 관리자가 충전 신청을 삭제할 때
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.auth.emoney.deposits.delete', $depositId));

        // Then: 삭제가 성공해야 함
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // And: 충전 신청이 삭제되어야 함
        $this->assertDatabaseMissing('user_emoney_deposits', [
            'id' => $depositId,
        ]);

        // And: 관련 로그가 삭제되어야 함
        $this->assertDatabaseMissing('user_emoney_logs', [
            'reference_id' => $depositId,
            'reference_type' => 'deposit',
        ]);

        // And: 관리자 로그가 기록되어야 함
        $this->assertDatabaseHas('admin_user_logs', [
            'user_id' => $this->adminUser->id,
            'action' => 'delete',
            'event_type' => 'deposit_delete',
        ]);
    }

    /** @test */
    public function admin_cannot_delete_approved_deposit()
    {
        // Given: 승인된 충전 신청이 있을 때
        $depositId = DB::table('user_emoney_deposits')->insertGetId([
            'user_uuid' => $this->regularUser->uuid,
            'amount' => 30000,
            'status' => 'approved',
            'checked' => '1',
            'checked_by' => $this->adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When: 관리자가 승인된 충전을 삭제하려고 할 때
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.auth.emoney.deposits.delete', $depositId));

        // Then: 삭제가 거부되어야 함
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => '이미 승인된 충전 신청은 삭제할 수 없습니다.'
        ]);

        // And: 충전 신청이 여전히 존재해야 함
        $this->assertDatabaseHas('user_emoney_deposits', [
            'id' => $depositId,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function admin_cannot_delete_nonexistent_deposit()
    {
        // When: 존재하지 않는 충전 신청을 삭제하려고 할 때
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.auth.emoney.deposits.delete', 99999));

        // Then: 404 오류가 발생해야 함
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => '해당 충전 신청을 찾을 수 없습니다.'
        ]);
    }
}