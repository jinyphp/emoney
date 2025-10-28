<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_emoney_deposits', function (Blueprint $table) {
            // SQLite는 ENUM 변경이 어려우므로 CHECK 제약조건은 건드리지 않고 새 값들을 허용
            // 어플리케이션 레벨에서 새로운 상태값들을 관리

            // 취소 관련 필드 추가
            $table->text('cancel_reason')->nullable()->comment('취소 사유');
            $table->timestamp('cancelled_at')->nullable()->comment('취소 요청 시간');

            // 환불 계좌 정보 필드 추가
            $table->unsignedBigInteger('refund_account_id')->nullable()->comment('환불 계좌 ID');
            $table->string('refund_bank_name', 100)->nullable()->comment('환불 은행명');
            $table->string('refund_account_number', 50)->nullable()->comment('환불 계좌번호');
            $table->string('refund_account_holder', 100)->nullable()->comment('환불 계좌 예금주');
            $table->timestamp('refunded_at')->nullable()->comment('환불 완료 시간');

            // 추가 필드들
            $table->string('bank_code', 10)->nullable()->comment('은행 코드');
            $table->date('deposit_date')->nullable()->comment('입금 날짜');

            // 새로운 인덱스 추가
            $table->index('cancelled_at');
            $table->index('refunded_at');
            $table->index(['status', 'cancelled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_emoney_deposits', function (Blueprint $table) {
            // 추가된 필드들 제거
            $table->dropColumn([
                'cancel_reason',
                'cancelled_at',
                'refund_account_id',
                'refund_bank_name',
                'refund_account_number',
                'refund_account_holder',
                'refunded_at',
                'bank_code',
                'deposit_date'
            ]);
        });
    }
};