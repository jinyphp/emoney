<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 사용자 이머니 출금 내역 테이블
 *
 * 사용자의 이머니 출금 요청 및 처리 내역을 관리하는 테이블
 * 출금 은행 계좌 정보, 출금 금액, 승인 상태 등을 저장
 */
return new class extends Migration
{
    /**
     * 마이그레이션 실행
     *
     * 이머니 출금 내역을 관리하는 테이블을 생성합니다.
     * - 샤딩된 사용자 시스템 지원
     * - 출금 은행 계좌 정보 저장
     * - 출금 승인 프로세스 지원
     * - 환율 정보 저장
     */
    public function up(): void
    {
        Schema::create('user_emoney_withdraw', function (Blueprint $table) {
            // 기본 필드
            $table->id()->comment('출금 내역 고유 ID');
            $table->timestamps();

            // 사용자 식별 (샤딩 지원)
            $table->string('user_uuid', 36)->index()->comment('사용자 UUID (샤딩용)');
            $table->integer('shard_id')->nullable()->index()->comment('샤드 번호 (0-15)');

            // 캐시된 사용자 정보
            $table->string('email')->nullable()->comment('사용자 이메일 (캐시)');
            $table->string('user_id')->nullable()->comment('사용자 ID (레거시)');

            // 출금 금액 정보
            $table->decimal('amount', 15, 2)->comment('출금 금액');
            $table->decimal('fee', 15, 2)->default(0)->comment('출금 수수료');
            $table->decimal('actual_amount', 15, 2)->comment('실제 출금 금액 (amount - fee)');
            $table->string('currency', 10)->default('KRW')->comment('출금 통화');
            $table->decimal('currency_rate', 10, 4)->default(1.0000)->comment('환율');

            // 출금 은행 정보
            $table->unsignedBigInteger('bank_id')->nullable()->comment('은행 ID (auth_banks 테이블 참조)');
            $table->string('bank')->comment('은행명 (캐시)');
            $table->string('account')->comment('계좌번호');
            $table->string('owner')->comment('예금주명');

            // 추가 정보
            $table->text('description')->nullable()->comment('출금 설명/메모');
            $table->string('log_id')->nullable()->comment('연결된 로그 ID');
            $table->string('transaction_id')->nullable()->comment('은행 거래 ID');

            // 승인 관리
            $table->boolean('checked')->default(false)->comment('관리자 확인 여부');
            $table->timestamp('checked_at')->nullable()->comment('확인 일시');
            $table->unsignedBigInteger('checked_by')->nullable()->comment('확인한 관리자 ID');

            // 처리 정보
            $table->timestamp('processed_at')->nullable()->comment('처리 완료 일시');
            $table->text('reject_reason')->nullable()->comment('거부 사유');

            // 상태 관리
            $table->enum('status', ['pending', 'approved', 'processing', 'completed', 'rejected', 'cancelled'])
                  ->default('pending')
                  ->index()
                  ->comment('출금 상태');

            // 인덱스
            $table->index(['user_uuid', 'status'], 'idx_user_status');
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index('amount', 'idx_amount');

            // 외래키 (선택사항)
            // $table->foreign('bank_id')->references('id')->on('auth_banks')->nullOnDelete();
        });
    }

    /**
     * 마이그레이션 롤백
     *
     * user_emoney_withdraw 테이블을 삭제합니다.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_emoney_withdraw');
    }
};
