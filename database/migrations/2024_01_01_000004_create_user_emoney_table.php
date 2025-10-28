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
        Schema::create('user_emoney', function (Blueprint $table) {
            $table->id();
            $table->string('user_uuid')->unique()->index();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('total_deposit', 15, 2)->default(0);
            $table->decimal('total_used', 15, 2)->default(0);
            $table->decimal('total_withdrawn', 15, 2)->default(0);
            $table->decimal('points', 15, 2)->default(0);
            $table->string('currency', 10)->default('KRW');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->index();
            $table->timestamps();

            // 인덱스
            $table->index(['status', 'created_at']);
            $table->index('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_emoney');
    }
};