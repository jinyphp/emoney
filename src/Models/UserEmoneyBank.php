<?php

namespace Jiny\AuthEmoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class UserEmoneyBank extends Model
{
    use HasFactory;

    protected $table = 'user_emoney_bank';

    protected $fillable = [
        'enable',
        'default',
        'email',
        'user_id',
        'type',
        'currency',
        'swift',
        'bank',
        'account',
        'owner',
        'description',
        'status'
    ];

    protected $casts = [
        'enable' => 'boolean',
        'default' => 'boolean',
    ];

    /**
     * 사용자 관계
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 기본 계좌 설정
     */
    public function setAsDefault()
    {
        // 기존 기본 계좌 해제
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['default' => false]);

        // 현재 계좌를 기본으로 설정
        $this->default = true;
        $this->save();

        return $this;
    }

    /**
     * 활성 상태 확인
     */
    public function isActive()
    {
        return $this->status === 'active' && $this->enable;
    }

    /**
     * 기본 계좌 확인
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * 사용자별 기본 계좌 조회
     */
    public static function getDefaultForUser($userId)
    {
        return static::where('user_id', $userId)
            ->where('default', true)
            ->where('enable', true)
            ->where('status', 'active')
            ->first();
    }

    /**
     * 사용자별 활성 계좌 목록
     */
    public static function getActiveForUser($userId)
    {
        return static::where('user_id', $userId)
            ->where('enable', true)
            ->where('status', 'active')
            ->orderBy('default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}