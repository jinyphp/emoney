<?php

namespace Jiny\AuthEmoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserEmoneyLog extends Model
{
    use HasFactory;

    protected $table = 'user_emoney_log';

    protected $fillable = [
        'user_id',
        'email',
        'type',
        'trans',
        'amount',
        'balance',
        'point',
        'fee',
        'tax',
        'currency',
        'exchange',
        'description',
        'reference',
        'method',
        'account',
        'bank',
        'status',
        'processed_at',
        'ip'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'exchange' => 'decimal:4',
        'point' => 'integer',
        'processed_at' => 'datetime',
    ];

    /**
     * 사용자 관계
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 전자지갑 관계
     */
    public function wallet()
    {
        return $this->belongsTo(UserEmoney::class, 'user_id', 'user_id');
    }

    /**
     * 거래 타입 레이블
     */
    public function getTypeLabel()
    {
        $types = [
            'deposit' => '입금',
            'withdraw' => '출금',
            'transfer' => '송금',
            'receive' => '수신',
            'purchase' => '구매',
            'refund' => '환불',
            'point_add' => '포인트 적립',
            'point_use' => '포인트 사용',
            'point_convert' => '포인트 전환',
            'fee' => '수수료',
            'adjustment' => '조정',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * 상태 레이블
     */
    public function getStatusLabel()
    {
        $statuses = [
            'pending' => '대기중',
            'processing' => '처리중',
            'completed' => '완료',
            'failed' => '실패',
            'cancelled' => '취소',
            'refunded' => '환불됨',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * 성공 여부
     */
    public function isSuccessful()
    {
        return $this->status === 'completed';
    }

    /**
     * 대기 중 여부
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * 실패 여부
     */
    public function isFailed()
    {
        return in_array($this->status, ['failed', 'cancelled']);
    }
}