<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 포인트 거래 이력 테이블
     */
    public function up(): void
    {
        Schema::create('user_point_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // 사용자 ID
            $table->string('user_uuid', 36)->nullable()->index()->comment('User UUID for sharding');
            $table->integer('shard_id')->nullable()->index()->comment('Shard number (0-15)');
            $table->enum('transaction_type', ['earn', 'use', 'refund', 'expire', 'admin']); // 거래 유형
            $table->decimal('amount', 15, 2); // 거래 금액 (양수: 적립, 음수: 사용)
            $table->decimal('balance_before', 15, 2); // 거래 전 잔액
            $table->decimal('balance_after', 15, 2); // 거래 후 잔액
            $table->string('reason')->nullable(); // 거래 사유
            $table->string('reference_type')->nullable(); // 참조 타입 (Order, Review 등)
            $table->unsignedBigInteger('reference_id')->nullable(); // 참조 ID
            $table->timestamp('expires_at')->nullable(); // 만료 시간
            $table->unsignedBigInteger('admin_id')->nullable(); // 관리자 ID (관리자 지급/차감)
            $table->string('admin_uuid', 36)->nullable()->index()->comment('Admin UUID for sharding');
            $table->integer('admin_shard_id')->nullable()->index()->comment('Admin Shard number (0-15)');
            $table->json('metadata')->nullable(); // 추가 메타데이터
            $table->timestamps();

            // 인덱스
            $table->index('user_id');
            $table->index('transaction_type');
            $table->index(['reference_type', 'reference_id']);
            $table->index('expires_at');
            $table->index('created_at');

            // 외래키
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_point_log');
    }
};