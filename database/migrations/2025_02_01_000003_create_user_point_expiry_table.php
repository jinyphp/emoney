<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 포인트 만료 관리 테이블
 *
 * 포인트 만료 스케줄 및 만료 처리 상태를 관리하는 테이블
 * 적립된 포인트의 만료일과 알림 발송 상태를 추적
 */
return new class extends Migration
{
    /**
     * 마이그레이션 실행
     *
     * 포인트 만료 관리를 위한 테이블을 생성합니다.
     * - 적립된 포인트별 만료일 관리
     * - 만료 예정 알림 기능
     * - 샤딩된 사용자 시스템 지원
     * - 자동 만료 처리 지원
     */
    public function up(): void
    {
        Schema::create('user_point_expiry', function (Blueprint $table) {
            // 기본 필드
            $table->id()->comment('포인트 만료 관리 고유 ID');
            $table->timestamps();

            // 사용자 식별 (샤딩 지원)
            $table->string('user_uuid', 36)->index()->comment('사용자 UUID (샤딩용)');
            $table->integer('shard_id')->nullable()->index()->comment('샤드 번호 (0-15)');

            // 레거시 지원
            $table->unsignedBigInteger('user_id')->nullable()->comment('사용자 ID (레거시)');

            // 만료 대상 포인트 정보
            $table->unsignedBigInteger('point_log_id')->comment('연관된 포인트 로그 ID');
            $table->integer('amount')->comment('만료 예정 포인트 수량');
            $table->integer('remaining_amount')->comment('현재 남은 포인트 수량');

            // 만료 일정
            $table->timestamp('expires_at')->index()->comment('만료 예정 일시');
            $table->integer('days_until_expiry')->nullable()->comment('만료까지 남은 일수 (캐시)');

            // 만료 처리 상태
            $table->boolean('is_expired')->default(false)->index()->comment('만료 처리 완료 여부');
            $table->timestamp('expired_at')->nullable()->comment('실제 만료 처리 시간');
            $table->integer('expired_amount')->default(0)->comment('실제 만료된 포인트 수량');

            // 알림 관리
            $table->boolean('notification_sent')->default(false)->comment('만료 예정 알림 발송 여부');
            $table->timestamp('notification_sent_at')->nullable()->comment('알림 발송 시간');
            $table->integer('notification_days_before')->nullable()->comment('몇 일 전에 알림 발송했는지');

            // 처리 상태
            $table->enum('status', ['active', 'expired', 'cancelled', 'partially_used'])
                  ->default('active')
                  ->index()
                  ->comment('만료 상태');

            // 추가 정보
            $table->string('expiry_reason')->nullable()->comment('만료 사유');
            $table->json('metadata')->nullable()->comment('추가 메타데이터');

            // 성능 최적화 인덱스
            $table->index(['user_uuid', 'is_expired'], 'idx_user_expired');
            $table->index(['expires_at', 'is_expired'], 'idx_expiry_status');
            $table->index(['notification_sent', 'expires_at'], 'idx_notification_expiry');
            $table->index(['status', 'expires_at'], 'idx_status_expiry');

            // 외래키 제약조건 제거 (샤딩으로 인한 참조 무결성 문제)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('point_log_id')->references('id')->on('user_point_log')->onDelete('cascade');
        });
    }

    /**
     * 마이그레이션 롤백
     *
     * user_point_expiry 테이블을 삭제합니다.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_point_expiry');
    }
};