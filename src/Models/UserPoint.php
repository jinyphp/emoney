<?php

namespace Jiny\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class UserPoint extends Model
{
    use HasFactory;

    protected $table = 'user_point';

    protected $fillable = [
        'user_id',
        'user_uuid',
        'shard_id',
        'balance',
        'total_earned',
        'total_used',
        'total_expired',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_used' => 'decimal:2',
        'total_expired' => 'decimal:2',
    ];

    /**
     * 사용자 관계
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 포인트 로그 관계
     */
    public function logs()
    {
        return $this->hasMany(UserPointLog::class, 'user_id', 'user_id');
    }

    /**
     * 만료 예정 포인트 관계
     */
    public function expiries()
    {
        return $this->hasMany(UserPointExpiry::class, 'user_id', 'user_id');
    }

    /**
     * 포인트 적립
     */
    public function earnPoints($amount, $reason = null, $referenceType = null, $referenceId = null, $expiresAt = null, $adminId = null)
    {
        $balanceBefore = $this->balance;
        $this->balance += $amount;
        $this->total_earned += $amount;
        $this->save();

        // 로그 생성
        $log = UserPointLog::create([
            'user_id' => $this->user_id,
            'user_uuid' => $this->user_uuid,
            'shard_id' => $this->shard_id,
            'transaction_type' => 'earn',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'expires_at' => $expiresAt,
            'admin_id' => $adminId,
        ]);

        // 만료 스케줄 생성 (만료일이 있는 경우)
        if ($expiresAt) {
            UserPointExpiry::create([
                'user_id' => $this->user_id,
                'user_uuid' => $this->user_uuid,
                'shard_id' => $this->shard_id,
                'point_log_id' => $log->id,
                'amount' => $amount,
                'expires_at' => $expiresAt,
            ]);
        }

        return $log;
    }

    /**
     * 포인트 사용
     */
    public function usePoints($amount, $reason = null, $referenceType = null, $referenceId = null, $adminId = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('사용 가능한 포인트가 부족합니다.');
        }

        $balanceBefore = $this->balance;
        $this->balance -= $amount;
        $this->total_used += $amount;
        $this->save();

        // 로그 생성
        return UserPointLog::create([
            'user_id' => $this->user_id,
            'user_uuid' => $this->user_uuid,
            'shard_id' => $this->shard_id,
            'transaction_type' => 'use',
            'amount' => -$amount, // 음수로 저장
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'admin_id' => $adminId,
        ]);
    }

    /**
     * 포인트 환불
     */
    public function refundPoints($amount, $reason = null, $referenceType = null, $referenceId = null, $adminId = null)
    {
        $balanceBefore = $this->balance;
        $this->balance += $amount;
        $this->total_used -= $amount; // 사용량에서 차감
        $this->save();

        // 로그 생성
        return UserPointLog::create([
            'user_id' => $this->user_id,
            'user_uuid' => $this->user_uuid,
            'shard_id' => $this->shard_id,
            'transaction_type' => 'refund',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'admin_id' => $adminId,
        ]);
    }

    /**
     * 포인트 만료 처리
     */
    public function expirePoints($amount, $reason = null, $adminId = null)
    {
        $balanceBefore = $this->balance;
        $this->balance -= $amount;
        $this->total_expired += $amount;
        $this->save();

        // 로그 생성
        return UserPointLog::create([
            'user_id' => $this->user_id,
            'user_uuid' => $this->user_uuid,
            'shard_id' => $this->shard_id,
            'transaction_type' => 'expire',
            'amount' => -$amount, // 음수로 저장
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'reason' => $reason ?: '포인트 만료',
            'admin_id' => $adminId,
        ]);
    }

    /**
     * 관리자 포인트 지급/차감
     */
    public function adminAdjustment($amount, $reason = null, $adminId = null)
    {
        $balanceBefore = $this->balance;
        $this->balance += $amount;

        if ($amount > 0) {
            $this->total_earned += $amount;
        } else {
            $this->total_used += abs($amount);
        }

        $this->save();

        // 로그 생성
        return UserPointLog::create([
            'user_id' => $this->user_id,
            'user_uuid' => $this->user_uuid,
            'shard_id' => $this->shard_id,
            'transaction_type' => 'admin',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'reason' => $reason ?: '관리자 조정',
            'admin_id' => $adminId,
        ]);
    }

    /**
     * 사용자별 포인트 계정 생성 또는 조회
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
                'user_uuid' => $user->uuid,
                'shard_id' => $user->shard_id,
                'balance' => 0,
                'total_earned' => 0,
                'total_used' => 0,
                'total_expired' => 0,
            ]
        );
    }

    /**
     * 만료 예정 포인트 조회
     */
    public function getExpiringPoints($days = 30)
    {
        return $this->expiries()
            ->where('expired', false)
            ->where('expires_at', '<=', now()->addDays($days))
            ->orderBy('expires_at')
            ->get();
    }
}