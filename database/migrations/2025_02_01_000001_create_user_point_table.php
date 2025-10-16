<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 사용자 포인트 테이블
     */
    public function up(): void
    {
        Schema::create('user_point', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique(); // 사용자 ID
            $table->string('user_uuid', 36)->nullable()->index()->comment('User UUID for sharding');
            $table->integer('shard_id')->nullable()->index()->comment('Shard number (0-15)');
            $table->decimal('balance', 15, 2)->default(0); // 현재 잔액
            $table->decimal('total_earned', 15, 2)->default(0); // 총 적립액
            $table->decimal('total_used', 15, 2)->default(0); // 총 사용액
            $table->decimal('total_expired', 15, 2)->default(0); // 총 만료액
            $table->timestamps();

            // 인덱스
            $table->index('user_id');
            $table->index('balance');

            // 외래키
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_point');
    }
};