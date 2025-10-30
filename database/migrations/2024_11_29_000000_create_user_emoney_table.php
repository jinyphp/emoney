<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 사용자 이머니 계정 테이블
 *
 * 샤딩된 사용자 시스템을 위한 전자지갑 테이블
 * UUID를 통해 사용자와 연결되며, 잔액, 포인트, 거래 내역을 관리
 */
return new class extends Migration
{
    /**
     * 마이그레이션 실행
     *
     * 사용자별 이머니 계정을 관리하는 테이블을 생성합니다.
     * - 샤딩된 사용자 시스템 지원 (UUID 기반)
     * - 이머니 잔액 및 포인트 관리
     * - 거래 통계 정보 저장
     */
    public function up(): void
    {
        Schema::create('user_emoney', function (Blueprint $table) {
            // 기본 필드
            $table->id()->comment('이머니 계정 고유 ID');
            $table->timestamps();

            // 사용자 식별 (샤딩 지원)
            $table->string('user_uuid', 36)->unique()->index()->comment('사용자 UUID (샤딩용)');
            $table->integer('shard_id')->nullable()->index()->comment('샤드 번호 (0-15)');

            // 캐시된 사용자 정보 (성능 최적화용)
            $table->string('email')->nullable()->comment('사용자 이메일 (캐시)');
            $table->string('name')->nullable()->comment('사용자 이름 (캐시)');

            // 이머니 관련 필드
            $table->decimal('balance', 15, 2)->default(0)->comment('이머니 잔액');
            $table->decimal('total_deposit', 15, 2)->default(0)->comment('총 충전 금액');
            $table->decimal('total_used', 15, 2)->default(0)->comment('총 사용 금액');
            $table->decimal('total_withdrawn', 15, 2)->default(0)->comment('총 출금 금액');

            // 포인트 관련 필드
            $table->integer('points')->default(0)->comment('보유 포인트');

            // 시스템 설정
            $table->string('currency', 10)->default('KRW')->comment('기준 통화');
            $table->enum('status', ['active', 'inactive', 'suspended', 'blocked'])
                  ->default('active')
                  ->index()
                  ->comment('계정 상태');

            // 성능 최적화 인덱스
            $table->index(['status', 'created_at'], 'idx_emoney_status_created');
            $table->index('balance', 'idx_emoney_balance');
            $table->index(['shard_id', 'status'], 'idx_emoney_shard_status');
        });
    }

    /**
     * 마이그레이션 롤백
     *
     * user_emoney 테이블을 삭제합니다.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_emoney');
    }
};
