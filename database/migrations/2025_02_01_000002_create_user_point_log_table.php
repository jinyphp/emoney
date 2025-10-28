<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 사용자 포인트 거래 로그 테이블
 *
 * 모든 포인트 관련 거래 내역을 기록하는 로그 테이블
 * 포인트 적립, 사용, 만료, 환불 등 모든 변동 사항을 추적
 */
return new class extends Migration
{
    /**
     * 마이그레이션 실행
     *
     * 포인트 거래 로그를 관리하는 테이블을 생성합니다.
     * - 모든 포인트 거래 내역 추적
     * - 포인트 잔액 변동 이력
     * - 샤딩된 사용자 시스템 지원
     * - 포인트 만료 관리
     * - 감사(Audit) 로그 기능
     */
    public function up(): void
    {
        Schema::create('user_point_log', function (Blueprint $table) {
            // 기본 필드
            $table->id()->comment('포인트 로그 고유 ID');
            $table->timestamps();

            // 사용자 식별 (샤딩 지원)
            $table->string('user_uuid', 36)->index()->comment('사용자 UUID (샤딩용)');
            $table->integer('shard_id')->nullable()->index()->comment('샤드 번호 (0-15)');

            // 레거시 지원
            $table->unsignedBigInteger('user_id')->nullable()->comment('사용자 ID (레거시)');

            // 거래 유형
            $table->enum('transaction_type', [
                'earn',         // 포인트 적립
                'use',          // 포인트 사용
                'refund',       // 포인트 환불
                'expire',       // 포인트 만료
                'admin_add',    // 관리자 지급
                'admin_deduct', // 관리자 차감
                'transfer_in',  // 포인트 받기 (송금)
                'transfer_out', // 포인트 보내기 (송금)
                'convert'       // 포인트 전환 (이머니로 등)
            ])->index()->comment('거래 유형');

            // 포인트 금액 정보
            $table->integer('amount')->comment('거래 포인트 (양수: 적립, 음수: 차감)');
            $table->integer('balance_before')->comment('거래 전 포인트 잔액');
            $table->integer('balance_after')->comment('거래 후 포인트 잔액');

            // 거래 정보
            $table->string('reason')->nullable()->comment('거래 사유/설명');
            $table->string('reference_type')->nullable()->comment('참조 타입 (Order, Review, Product 등)');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('참조 테이블 ID');

            // 포인트 만료 정보
            $table->timestamp('expires_at')->nullable()->comment('포인트 만료 시간');
            $table->boolean('is_expired')->default(false)->comment('만료 여부');

            // 관리자 정보 (관리자가 처리한 경우)
            $table->unsignedBigInteger('admin_id')->nullable()->comment('처리한 관리자 ID');
            $table->string('admin_uuid', 36)->nullable()->index()->comment('관리자 UUID (샤딩용)');
            $table->integer('admin_shard_id')->nullable()->index()->comment('관리자 샤드 번호');

            // 추가 메타데이터
            $table->json('metadata')->nullable()->comment('추가 메타데이터 (JSON)');

            // 상태 관리
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])
                  ->default('completed')
                  ->index()
                  ->comment('거래 상태');

            // 성능 최적화 인덱스
            $table->index(['user_uuid', 'transaction_type'], 'idx_user_type');
            $table->index(['transaction_type', 'created_at'], 'idx_type_created');
            $table->index(['reference_type', 'reference_id'], 'idx_reference');
            $table->index('expires_at', 'idx_expires');
            $table->index(['is_expired', 'expires_at'], 'idx_expired_at');

            // 외래키 제약조건 제거 (샤딩으로 인한 참조 무결성 문제)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * 마이그레이션 롤백
     *
     * user_point_log 테이블을 삭제합니다.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_point_log');
    }
};