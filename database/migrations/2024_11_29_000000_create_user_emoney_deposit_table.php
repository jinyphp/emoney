<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 사용자 이머니 충전 내역 테이블
 *
 * 사용자의 이머니 충전 요청 및 처리 내역을 관리하는 테이블
 * 은행 계좌 정보, 충전 금액, 승인 상태 등을 저장
 */
return new class extends Migration
{
    /**
     * 마이그레이션 실행
     *
     * 이머니 충전 내역을 관리하는 테이블을 생성합니다.
     * - 샤딩된 사용자 시스템 지원
     * - 은행 계좌 정보 저장
     * - 충전 승인 프로세스 지원
     * - 환율 정보 저장
     */
    public function up(): void
    {
        Schema::create('user_emoney_deposit', function (Blueprint $table) {
            // 기본 필드
            $table->id()->comment('충전 내역 고유 ID');
            $table->timestamps();

            // 사용자 식별 (샤딩 지원)
            $table->string('user_uuid', 36)->index()->comment('사용자 UUID (샤딩용)');
            $table->integer('shard_id')->nullable()->index()->comment('샤드 번호 (0-15)');

            // 캐시된 사용자 정보
            $table->string('email')->nullable()->comment('사용자 이메일 (캐시)');
            $table->string('user_id')->nullable()->comment('사용자 ID (레거시)');

            // 충전 금액 정보
            $table->decimal('amount', 15, 2)->comment('충전 금액');
            $table->string('currency', 10)->default('KRW')->comment('충전 통화');
            $table->decimal('currency_rate', 10, 4)->default(1.0000)->comment('환율');

            // 은행 정보
            $table->unsignedBigInteger('bank_id')->nullable()->comment('은행 ID (auth_banks 테이블 참조)');
            $table->string('bank')->comment('은행명 (캐시)');
            $table->string('bank_code', 10)->nullable()->comment('은행 코드 (KR 표준)');
            $table->string('account')->comment('계좌번호');
            $table->string('owner')->comment('예금주명');

            // 입금 정보
            $table->date('deposit_date')->nullable()->comment('실제 입금 날짜');
            $table->string('deposit_reference')->nullable()->comment('입금 참조번호');

            // 추가 정보
            $table->text('description')->nullable()->comment('충전 설명/메모');
            $table->string('log_id')->nullable()->comment('연결된 로그 ID');

            // 승인 관리
            $table->boolean('checked')->default(false)->comment('관리자 확인 여부');
            $table->timestamp('checked_at')->nullable()->comment('확인 일시');
            $table->unsignedBigInteger('checked_by')->nullable()->comment('확인한 관리자 ID');

            // 취소 관리
            $table->text('cancel_reason')->nullable()->comment('충전 취소 사유');
            $table->timestamp('cancelled_at')->nullable()->comment('취소 요청 시간');
            $table->unsignedBigInteger('cancelled_by')->nullable()->comment('취소 처리한 관리자 ID');

            // 환불 계좌 정보
            $table->unsignedBigInteger('refund_account_id')->nullable()->comment('환불 계좌 ID (user_emoney_bank 참조)');
            $table->string('refund_bank_name', 100)->nullable()->comment('환불 은행명');
            $table->string('refund_account_number', 50)->nullable()->comment('환불 계좌번호');
            $table->string('refund_account_holder', 100)->nullable()->comment('환불 계좌 예금주');

            // 환불 처리 정보
            $table->decimal('refund_amount', 15, 2)->nullable()->comment('환불 금액');
            $table->decimal('refund_fee', 15, 2)->default(0)->comment('환불 수수료');
            $table->timestamp('refunded_at')->nullable()->comment('환불 완료 시간');
            $table->unsignedBigInteger('refunded_by')->nullable()->comment('환불 처리한 관리자 ID');
            $table->string('refund_transaction_id')->nullable()->comment('환불 거래 ID');

            // 상태 관리
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'refunded', 'refund_pending'])
                  ->default('pending')
                  ->index()
                  ->comment('충전 상태');

            // 성능 최적화 인덱스
            $table->index(['user_uuid', 'status'], 'idx_user_status');
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index('amount', 'idx_amount');
            $table->index('cancelled_at', 'idx_cancelled_at');
            $table->index('refunded_at', 'idx_refunded_at');
            $table->index(['status', 'cancelled_at'], 'idx_status_cancelled');
            $table->index(['bank_code', 'deposit_date'], 'idx_bank_deposit');

            // 외래키 (선택사항)
            // $table->foreign('bank_id')->references('id')->on('auth_banks')->nullOnDelete();
        });
    }

    /**
     * 마이그레이션 롤백
     *
     * user_emoney_deposit 테이블을 삭제합니다.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_emoney_deposit');
    }
};
