<?php

namespace Jiny\Auth\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserEmoneyDeposit extends Model
{
    use HasFactory;

    protected $table = 'user_emoney_deposit';

    protected $fillable = [
        'user_id',
        'email',
        'trans',
        'amount',
        'currency',
        'exchange',
        'method',
        'bank',
        'account',
        'depositor',
        'description',
        'reference',
        'confirmed_at',
        'status',
        'admin_id',
        'admin_memo',
        'ip'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange' => 'decimal:4',
        'confirmed_at' => 'datetime',
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
     * 입금 확인
     */
    public function confirm($adminId = null, $memo = null)
    {
        if ($this->status === 'confirmed') {
            throw new \Exception('이미 확인된 입금입니다.');
        }

        $this->status = 'confirmed';
        $this->confirmed_at = now();
        $this->admin_id = $adminId;
        $this->admin_memo = $memo;
        $this->save();

        // 전자지갑 잔액 추가
        $wallet = UserEmoney::findOrCreateForUser($this->user_id);
        $wallet->addBalance($this->amount, "입금: {$this->trans}");

        return $this;
    }

    /**
     * 입금 취소
     */
    public function cancel($adminId = null, $memo = null)
    {
        if ($this->status === 'cancelled') {
            throw new \Exception('이미 취소된 입금입니다.');
        }

        if ($this->status === 'confirmed') {
            // 확인된 입금인 경우 잔액 차감
            $wallet = UserEmoney::findOrCreateForUser($this->user_id);
            $wallet->subtractBalance($this->amount, "입금 취소: {$this->trans}");
        }

        $this->status = 'cancelled';
        $this->admin_id = $adminId;
        $this->admin_memo = $memo;
        $this->save();

        return $this;
    }

    /**
     * 입금 방법 레이블
     */
    public function getMethodLabel()
    {
        $methods = [
            'bank' => '무통장입금',
            'card' => '신용카드',
            'virtual' => '가상계좌',
            'mobile' => '휴대폰',
            'point' => '포인트전환',
            'admin' => '관리자충전',
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
            'confirmed' => '확인완료',
            'cancelled' => '취소됨',
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}