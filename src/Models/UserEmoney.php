<?php

namespace Jiny\Auth\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class UserEmoney extends Model
{
    use HasFactory;

    protected $table = 'user_emoney';

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'currency',
        'balance',
        'point',
        'status'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'point' => 'integer',
    ];

    /**
     * 사용자 관계
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 입금 기록
     */
    public function deposits()
    {
        return $this->hasMany(UserEmoneyDeposit::class, 'user_id', 'user_id');
    }

    /**
     * 출금 기록
     */
    public function withdrawals()
    {
        return $this->hasMany(UserEmoneyWithdraw::class, 'user_id', 'user_id');
    }

    /**
     * 거래 로그
     */
    public function logs()
    {
        return $this->hasMany(UserEmoneyLog::class, 'user_id', 'user_id');
    }

    /**
     * 잔액 추가
     */
    public function addBalance($amount, $description = null)
    {
        $this->balance = bcadd($this->balance, $amount, 2);
        $this->save();

        // 로그 기록
        UserEmoneyLog::create([
            'user_id' => $this->user_id,
            'type' => 'deposit',
            'amount' => $amount,
            'balance' => $this->balance,
            'description' => $description,
            'status' => 'completed',
        ]);

        return $this;
    }

    /**
     * 잔액 차감
     */
    public function subtractBalance($amount, $description = null)
    {
        if (bccomp($this->balance, $amount, 2) < 0) {
            throw new \Exception('잔액이 부족합니다.');
        }

        $this->balance = bcsub($this->balance, $amount, 2);
        $this->save();

        // 로그 기록
        UserEmoneyLog::create([
            'user_id' => $this->user_id,
            'type' => 'withdraw',
            'amount' => $amount,
            'balance' => $this->balance,
            'description' => $description,
            'status' => 'completed',
        ]);

        return $this;
    }

    /**
     * 포인트 추가
     */
    public function addPoints($points, $description = null)
    {
        $this->point += $points;
        $this->save();

        // 로그 기록
        UserEmoneyLog::create([
            'user_id' => $this->user_id,
            'type' => 'point_add',
            'amount' => $points,
            'balance' => $this->balance,
            'point' => $this->point,
            'description' => $description,
            'status' => 'completed',
        ]);

        return $this;
    }

    /**
     * 포인트 사용
     */
    public function usePoints($points, $description = null)
    {
        if ($this->point < $points) {
            throw new \Exception('포인트가 부족합니다.');
        }

        $this->point -= $points;
        $this->save();

        // 로그 기록
        UserEmoneyLog::create([
            'user_id' => $this->user_id,
            'type' => 'point_use',
            'amount' => $points,
            'balance' => $this->balance,
            'point' => $this->point,
            'description' => $description,
            'status' => 'completed',
        ]);

        return $this;
    }

    /**
     * 활성 상태 확인
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * 사용자별 전자지갑 생성 또는 조회
     */
    public static function findOrCreateForUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception('사용자를 찾을 수 없습니다.');
        }

        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'email' => $user->email,
                'name' => $user->name,
                'currency' => 'KRW',
                'balance' => '0.00',
                'point' => 0,
                'status' => 'active',
            ]
        );
    }
}