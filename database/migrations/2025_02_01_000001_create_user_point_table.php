<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 사용자 포인트 계정 테이블
 *
 * 샤딩된 사용자 시스템을 위한 포인트 관리 테이블
 * 포인트 잔액, 적립/사용/만료 내역 통계를 관리
 */
return new class extends Migration
{
    /**
     * 마이그레이션 실행
     *
     * 사용자별 포인트 계정을 관리하는 테이블을 생성합니다.
     * - 샤딩된 사용자 시스템 지원 (UUID 기반)
     * - 포인트 잔액 및 사용 내역 관리
     * - 만료 포인트 추적
     * - 포인트 통계 정보 저장
     */
    public function up(): void
    {
        Schema::create('user_point', function (Blueprint $table) {
            // 기본 필드
            $table->id()->comment('포인트 계정 고유 ID');
            $table->timestamps();

            // 사용자 식별 (샤딩 지원)
            $table->string('user_uuid', 36)->unique()->index()->comment('사용자 UUID (샤딩용)');
            $table->integer('shard_id')->nullable()->index()->comment('샤드 번호 (0-15)');

            // 캐시된 사용자 정보 (성능 최적화용)
            $table->string('email')->nullable()->comment('사용자 이메일 (캐시)');
            $table->string('name')->nullable()->comment('사용자 이름 (캐시)');

            // 포인트 잔액 정보
            $table->integer('balance')->default(0)->comment('현재 포인트 잔액');
            $table->integer('available_balance')->default(0)->comment('사용 가능한 포인트 (만료일 고려)');

            // 포인트 통계
            $table->integer('total_earned')->default(0)->comment('총 적립 포인트');
            $table->integer('total_used')->default(0)->comment('총 사용 포인트');
            $table->integer('total_expired')->default(0)->comment('총 만료 포인트');
            $table->integer('total_refunded')->default(0)->comment('총 환불 포인트');

            // 만료 관련 정보
            $table->integer('expiring_soon')->default(0)->comment('곧 만료될 포인트 (7일 이내)');
            $table->date('next_expiry_date')->nullable()->comment('다음 만료 예정일');

            // 시스템 설정
            $table->enum('status', ['active', 'inactive', 'suspended', 'blocked'])
                  ->default('active')
                  ->index()
                  ->comment('포인트 계정 상태');

            // 레거시 지원 (기존 시스템 호환성)
            $table->unsignedBigInteger('user_id')->nullable()->comment('사용자 ID (레거시)');

            // 성능 최적화 인덱스
            $table->index(['status', 'created_at'], 'idx_point_status_created');
            $table->index('balance', 'idx_balance');
            $table->index(['shard_id', 'status'], 'idx_shard_status');
            $table->index('next_expiry_date', 'idx_expiry_date');

            // 외래키 제약조건 제거 (샤딩으로 인한 참조 무결성 문제)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * 마이그레이션 롤백
     *
     * user_point 테이블을 삭제합니다.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_point');
    }
};