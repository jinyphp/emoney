<?php

namespace Jiny\Auth\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPointExpiry extends Model
{
    use HasFactory;

    protected $table = 'user_point_expiry';

    protected $fillable = [
        'user_id',
        'user_uuid',
        'shard_id',
        'point_log_id',
        'amount',
        'expires_at',
        'expired',
        'expired_at',
        'notified',
        'notified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'expired' => 'boolean',
        'expired_at' => 'datetime',
        'notified' => 'boolean',
        'notified_at' => 'datetime',
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
    public function pointLog()
    {
        return $this->belongsTo(UserPointLog::class, 'point_log_id');
    }

    /**
     * 만료되지 않은 포인트 스코프
     */
    public function scopeNotExpired($query)
    {
        return $query->where('expired', false);
    }

    /**
     * 만료된 포인트 스코프
     */
    public function scopeExpired($query)
    {
        return $query->where('expired', true);
    }

    /**
     * 만료 예정 포인트 스코프
     */
    public function scopeExpiring($query, $days = 30)
    {
        return $query->where('expired', false)
            ->where('expires_at', '<=', now()->addDays($days));
    }

    /**
     * 오늘 만료 예정 포인트 스코프
     */
    public function scopeExpiringToday($query)
    {
        return $query->where('expired', false)
            ->whereDate('expires_at', today());
    }

    /**
     * 알림 미발송 스코프
     */
    public function scopeNotNotified($query)
    {
        return $query->where('notified', false);
    }

    /**
     * 포인트 만료 처리
     */
    public function processExpiry()
    {
        if ($this->expired) {
            return false;
        }

        // 사용자 포인트 정보 조회
        $userPoint = UserPoint::where('user_id', $this->user_id)->first();

        if (!$userPoint) {
            throw new \Exception('사용자 포인트 정보를 찾을 수 없습니다.');
        }

        // 만료 처리
        $userPoint->expirePoints($this->amount, "포인트 만료 (만료일: {$this->expires_at->format('Y-m-d')})");

        // 만료 상태 업데이트
        $this->expired = true;
        $this->expired_at = now();
        $this->save();

        return true;
    }

    /**
     * 만료 알림 발송 처리
     */
    public function markAsNotified()
    {
        $this->notified = true;
        $this->notified_at = now();
        $this->save();
    }

    /**
     * 만료까지 남은 일수
     */
    public function getDaysUntilExpiryAttribute()
    {
        if ($this->expired) {
            return 0;
        }

        return max(0, $this->expires_at->diffInDays(now(), false));
    }

    /**
     * 만료 여부 체크
     */
    public function isExpired()
    {
        return $this->expired || $this->expires_at->isPast();
    }

    /**
     * 만료 임박 여부 체크
     */
    public function isExpiringSoon($days = 7)
    {
        if ($this->expired) {
            return false;
        }

        return $this->expires_at->diffInDays(now()) <= $days;
    }

    /**
     * 사용자별 만료 예정 포인트 총액 조회
     */
    public static function getTotalExpiringAmount($userId, $days = 30)
    {
        return static::where('user_id', $userId)
            ->where('expired', false)
            ->where('expires_at', '<=', now()->addDays($days))
            ->sum('amount');
    }

    /**
     * 만료 처리 대상 일괄 조회
     */
    public static function getExpiredBatch($limit = 1000)
    {
        return static::where('expired', false)
            ->where('expires_at', '<=', now())
            ->limit($limit)
            ->get();
    }

    /**
     * 만료 알림 대상 일괄 조회
     */
    public static function getNotificationBatch($days = 7, $limit = 1000)
    {
        return static::where('expired', false)
            ->where('notified', false)
            ->where('expires_at', '<=', now()->addDays($days))
            ->where('expires_at', '>', now())
            ->limit($limit)
            ->get();
    }

    /**
     * 사용자별 만료 스케줄 조회
     */
    public static function getUserExpirySchedule($userId)
    {
        return static::where('user_id', $userId)
            ->where('expired', false)
            ->orderBy('expires_at')
            ->get()
            ->groupBy(function ($item) {
                return $item->expires_at->format('Y-m');
            });
    }
}