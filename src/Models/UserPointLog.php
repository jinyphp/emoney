<?php

namespace Jiny\AuthEmoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class UserPointLog extends Model
{
    use HasFactory;

    protected $table = 'user_point_log';

    protected $fillable = [
        'user_id',
        'user_uuid',
        'shard_id',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'reason',
        'reference_type',
        'reference_id',
        'expires_at',
        'admin_id',
        'admin_uuid',
        'admin_shard_id',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'expires_at' => 'datetime',
        'metadata' => 'json',
    ];

    /**
     * 사용자 관계
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 관리자 관계
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * 만료 정보 관계
     */
    public function expiry()
    {
        return $this->hasOne(UserPointExpiry::class, 'point_log_id');
    }

    /**
     * 거래 유형별 스코프
     */
    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * 적립 스코프
     */
    public function scopeEarned($query)
    {
        return $query->where('transaction_type', 'earn');
    }

    /**
     * 사용 스코프
     */
    public function scopeUsed($query)
    {
        return $query->where('transaction_type', 'use');
    }

    /**
     * 환불 스코프
     */
    public function scopeRefunded($query)
    {
        return $query->where('transaction_type', 'refund');
    }

    /**
     * 만료 스코프
     */
    public function scopeExpired($query)
    {
        return $query->where('transaction_type', 'expire');
    }

    /**
     * 관리자 조정 스코프
     */
    public function scopeAdminAdjustment($query)
    {
        return $query->where('transaction_type', 'admin');
    }

    /**
     * 참조 유형별 스코프
     */
    public function scopeByReference($query, $type, $id = null)
    {
        $query = $query->where('reference_type', $type);

        if ($id !== null) {
            $query->where('reference_id', $id);
        }

        return $query;
    }

    /**
     * 기간별 스코프
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * 거래 유형 한글명 반환
     */
    public function getTransactionTypeNameAttribute()
    {
        $types = [
            'earn' => '적립',
            'use' => '사용',
            'refund' => '환불',
            'expire' => '만료',
            'admin' => '관리자 조정',
        ];

        return $types[$this->transaction_type] ?? $this->transaction_type;
    }

    /**
     * 금액의 부호 체크
     */
    public function isPositive()
    {
        return $this->amount > 0;
    }

    /**
     * 금액의 부호 체크
     */
    public function isNegative()
    {
        return $this->amount < 0;
    }

    /**
     * 만료 가능 여부 체크
     */
    public function canExpire()
    {
        return $this->transaction_type === 'earn' && $this->expires_at && !$this->expiry;
    }

    /**
     * 포인트 사용량 통계
     */
    public static function getUsageStats($userId, $period = '1month')
    {
        $endDate = now();

        switch ($period) {
            case '1week':
                $startDate = now()->subWeek();
                break;
            case '1month':
                $startDate = now()->subMonth();
                break;
            case '3month':
                $startDate = now()->subMonths(3);
                break;
            case '1year':
                $startDate = now()->subYear();
                break;
            default:
                $startDate = now()->subMonth();
        }

        return [
            'earned' => static::where('user_id', $userId)
                ->where('transaction_type', 'earn')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount'),
            'used' => abs(static::where('user_id', $userId)
                ->where('transaction_type', 'use')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount')),
            'refunded' => static::where('user_id', $userId)
                ->where('transaction_type', 'refund')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount'),
            'expired' => abs(static::where('user_id', $userId)
                ->where('transaction_type', 'expire')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount')),
        ];
    }

    /**
     * 참조별 포인트 내역 조회
     */
    public static function getByReference($referenceType, $referenceId)
    {
        return static::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}