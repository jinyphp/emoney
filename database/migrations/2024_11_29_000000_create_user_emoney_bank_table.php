<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 사용자 등록 은행 계좌 테이블
 *
 * 사용자가 등록한 은행 계좌 정보를 관리하는 테이블
 * 충전/출금 시 사용할 계좌 정보를 저장
 */
return new class extends Migration
{
    /**
     * 마이그레이션 실행
     *
     * 사용자 은행 계좌 정보를 관리하는 테이블을 생성합니다.
     * - 샤딩된 사용자 시스템 지원
     * - 다중 계좌 등록 지원
     * - 기본 계좌 설정 기능
     * - 계좌 인증 상태 관리
     */
    public function up(): void
    {
        Schema::create('user_emoney_bank', function (Blueprint $table) {
            // 기본 필드
            $table->id()->comment('계좌 정보 고유 ID');
            $table->timestamps();

            // 사용자 식별 (샤딩 지원)
            $table->string('user_uuid', 36)->index()->comment('사용자 UUID (샤딩용)');
            $table->integer('shard_id')->nullable()->index()->comment('샤드 번호 (0-15)');

            // 캐시된 사용자 정보
            $table->string('email')->nullable()->comment('사용자 이메일 (캐시)');
            $table->string('user_id')->nullable()->comment('사용자 ID (레거시)');

            // 계좌 설정
            $table->boolean('is_enabled')->default(true)->comment('계좌 활성화 여부');
            $table->boolean('is_default')->default(false)->comment('기본 계좌 여부');

            // 계좌 유형
            $table->enum('type', ['deposit', 'withdraw', 'both'])
                  ->default('both')
                  ->comment('계좌 사용 용도 (입금/출금/둘다)');

            // 은행 정보
            $table->unsignedBigInteger('bank_id')->nullable()->comment('은행 ID (auth_banks 테이블 참조)');
            $table->string('bank_name')->comment('은행명 (캐시)');
            $table->string('swift_code', 11)->nullable()->comment('SWIFT 코드');
            $table->string('currency', 10)->default('KRW')->comment('계좌 통화');

            // 계좌 정보
            $table->string('account_number')->comment('계좌번호');
            $table->string('account_holder')->comment('예금주명');
            $table->string('account_type', 20)->default('SAVINGS')->comment('계좌 종류');

            // 인증 정보
            $table->boolean('is_verified')->default(false)->comment('계좌 인증 여부');
            $table->timestamp('verified_at')->nullable()->comment('인증 완료 일시');
            $table->string('verification_method')->nullable()->comment('인증 방법');

            // 추가 정보
            $table->text('description')->nullable()->comment('계좌 설명/메모');
            $table->json('metadata')->nullable()->comment('추가 메타데이터');

            // 상태 관리
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending_verification'])
                  ->default('pending_verification')
                  ->index()
                  ->comment('계좌 상태');

            // 인덱스
            $table->index(['user_uuid', 'is_default'], 'idx_user_default');
            $table->index(['user_uuid', 'type'], 'idx_user_type');
            $table->index(['status', 'is_enabled'], 'idx_status_enabled');
            $table->unique(['user_uuid', 'account_number'], 'unique_user_account');

            // 외래키 (선택사항)
            // $table->foreign('bank_id')->references('id')->on('auth_banks')->nullOnDelete();
        });
    }

    /**
     * 마이그레이션 롤백
     *
     * user_emoney_bank 테이블을 삭제합니다.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_emoney_bank');
    }
};
