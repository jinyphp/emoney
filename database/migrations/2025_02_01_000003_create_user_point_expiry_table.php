<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 포인트 만료 스케줄 테이블
     */
    public function up(): void
    {
        Schema::create('user_point_expiry', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // 사용자 ID
            $table->string('user_uuid', 36)->nullable()->index()->comment('User UUID for sharding');
            $table->integer('shard_id')->nullable()->index()->comment('Shard number (0-15)');
            $table->unsignedBigInteger('point_log_id'); // 포인트 로그 ID
            $table->decimal('amount', 15, 2); // 만료 예정 포인트
            $table->timestamp('expires_at'); // 만료 예정일
            $table->boolean('expired')->default(false); // 만료 처리 여부
            $table->timestamp('expired_at')->nullable(); // 실제 만료 처리 시간
            $table->boolean('notified')->default(false); // 알림 발송 여부
            $table->timestamp('notified_at')->nullable(); // 알림 발송 시간
            $table->timestamps();

            // 인덱스
            $table->index('user_id');
            $table->index('expires_at');
            $table->index('expired');
            $table->index(['user_id', 'expired', 'expires_at']);

            // 외래키
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('point_log_id')->references('id')->on('user_point_log')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_point_expiry');
    }
};