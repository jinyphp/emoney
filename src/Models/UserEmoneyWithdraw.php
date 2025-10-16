<?php

namespace Jiny\AuthEmoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserEmoneyWithdraw extends Model
{
    use HasFactory;

    protected $table = 'user_emoney_withdraw';

    protected $fillable = [
        'user_id',
        'email',
        'trans',
        'amount',
        'fee',
        'tax',
        'total',
        'currency',
        'exchange',
        'method',
        'bank',
        'account',
        'account_name',
        'description',
        'reference',
        'processed_at',
        'status',
        'admin_id',
        'admin_memo',
        'ip'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'exchange' => 'decimal:4',
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
     * 관리자 관계
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * 출금 승인
     */
    public function approve($adminId = null, $memo = null)
    {
        if ($this->status !== 'pending') {
            throw new \Exception('대기 중인 출금만 승인할 수 있습니다.');
        }

        $this->status = 'approved';
        $this->processed_at = now();
        $this->admin_id = $adminId;
        $this->admin_memo = $memo;
        $this->save();

        return $this;
    }

    /**
     * 출금 완료 처리
     */
    public function complete()
    {
        if ($this->status !== 'approved') {
            throw new \Exception('승인된 출금만 완료 처리할 수 있습니다.');
        }

        $this->status = 'completed';
        $this->processed_at = now();
        $this->save();

        return $this;
    }

    /**
     * 출금 거절
     */
    public function reject($adminId = null, $reason = null)
    {
        if (!in_array($this->status, ['pending', 'approved'])) {
            throw new \Exception('대기 중이거나 승인된 출금만 거절할 수 있습니다.');
        }

        // 잔액 복구
        $wallet = UserEmoney::findOrCreateForUser($this->user_id);
        $wallet->addBalance($this->total, "출금 거절: {$this->trans}");

        $this->status = 'rejected';
        $this->admin_id = $adminId;
        $this->admin_memo = $reason;
        $this->save();

        return $this;
    }

    /**
     * 출금 방법 레이블
     */
    public function getMethodLabel()
    {
        $methods = [
            'bank' => '계좌이체',
            'card' => '카드송금',
            'point' => '포인트전환',
            'crypto' => '암호화폐',
        ];

        return $methods[$this->method] ?? $this->method;
    }

    /**
     * 상태 레이블
     */
    public function getStatusLabel()
    {
        $statuses = [
            'pending' => '대기중',
            'approved' => '승인됨',
            'completed' => '완료',
            'rejected' => '거절됨',
            'cancelled' => '취소됨',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * 총액 계산
     */
    public function calculateTotal()
    {
        $this->total = bcadd($this->amount, bcadd($this->fee, $this->tax, 2), 2);
        return $this->total;
    }
}