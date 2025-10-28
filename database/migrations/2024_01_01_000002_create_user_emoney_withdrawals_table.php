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
        Schema::create('user_emoney_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('user_uuid')->index();
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->string('currency', 10)->default('KRW');
            $table->string('method', 50)->default('bank_transfer');
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('account_holder', 100)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->boolean('checked')->nullable()->index();
            $table->timestamp('checked_at')->nullable();
            $table->unsignedBigInteger('checked_by')->nullable();
            $table->text('admin_memo')->nullable();
            $table->text('user_memo')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->timestamps();

            // 인덱스
            $table->index(['user_uuid', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_emoney_withdrawals');
    }
};