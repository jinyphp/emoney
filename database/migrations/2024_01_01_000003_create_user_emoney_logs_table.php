<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_emoney_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_uuid')->index();
            $table->string('type', 50)->index(); // deposit, withdrawal, purchase, refund, etc.
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->timestamps();

            // 인덱스
            $table->index(['user_uuid', 'type']);
            $table->index(['user_uuid', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_emoney_logs');
    }
};