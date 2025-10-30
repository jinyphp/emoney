<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 사용자 이머니 거래 로그 테이블
 *
 * 모든 이머니 관련 거래 내역을 기록하는 로그 테이블
 * 잔액 변동, 포인트 변동, 입출금 내역 등을 추적
 */
return new class extends Migration
{
    /**
     * 마이그레이션 실행
     *
     * 이머니 거래 로그를 관리하는 테이블을 생성합니다.
     * - 모든 이머니 거래 내역 추적
     * - 잔액 및 포인트 변동 이력
     * - 샤딩된 사용자 시스템 지원
     * - 감사(Audit) 로그 기능
     */
    public function up(): void
    {
        Schema::create('user_emoney_log', function (Blueprint $table) {
            // 기본 필드
            $table->id()->comment('로그 고유 ID');
            $table->timestamps();

            // 사용자 식별 (샤딩 지원)
            $table->string('user_uuid', 36)->index()->comment('사용자 UUID (샤딩용)');
            $table->integer('shard_id')->nullable()->index()->comment('샤드 번호 (0-15)');

            // 캐시된 사용자 정보
            $table->string('email')->nullable()->comment('사용자 이메일 (캐시)');
            $table->string('user_id')->nullable()->comment('사용자 ID (레거시)');

            // 거래 유형
            $table->enum('type', [
                'deposit',           // 충전
                'withdraw',          // 출금
                'transfer_send',     // 송금 (보내기)
                'transfer_receive',  // 송금 (받기)
                'point_add',         // 포인트 적립
                'point_use',         // 포인트 사용
                'point_expire',      // 포인트 만료
                'admin_adjust',      // 관리자 조정
                'refund',           // 환불
                'fee'               // 수수료
            ])->index()->comment('거래 유형');

            // 금액 정보
            $table->decimal('amount', 15, 2)->default(0)->comment('거래 금액');
            $table->string('currency', 10)->default('KRW')->comment('거래 통화');
            $table->decimal('currency_rate', 10, 4)->default(1.0000)->comment('환율');

            // 잔액 정보 (거래 후)
            $table->decimal('balance_before', 15, 2)->comment('거래 전 잔액');
            $table->decimal('balance_after', 15, 2)->comment('거래 후 잔액');

            // 포인트 정보 (거래 후)
            $table->integer('points_before')->default(0)->comment('거래 전 포인트');
            $table->integer('points_after')->default(0)->comment('거래 후 포인트');

            // 관련 거래 정보
            $table->string('reference_table')->nullable()->comment('연관된 테이블명');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('연관된 테이블 ID');

            // 설명 및 메모
            $table->text('description')->nullable()->comment('거래 설명');
            $table->json('metadata')->nullable()->comment('추가 메타데이터 (JSON)');

            // 상태 정보
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])
                  ->default('completed')
                  ->index()
                  ->comment('거래 상태');

            // 관리자 정보 (관리자가 생성한 경우)
            $table->unsignedBigInteger('admin_id')->nullable()->comment('처리한 관리자 ID');

            // 인덱스
            $table->index(['user_uuid', 'type'], 'idx_log_user_type');
            $table->index(['type', 'created_at'], 'idx_log_type_created');
            $table->index(['reference_table', 'reference_id'], 'idx_log_reference');
            $table->index('amount', 'idx_log_amount');
        });
    }

    /**
     * 마이그레이션 롤백
     *
     * user_emoney_log 테이블을 삭제합니다.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_emoney_log');
    }
};
