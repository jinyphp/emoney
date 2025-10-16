<?php

namespace Jiny\Auth\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuthBank extends Model
{
    use HasFactory;

    protected $table = 'auth_banks';

    protected $fillable = [
        'name',
        'code',
        'country',
        'swift_code',
        'logo',
        'website',
        'phone',
        'description',
        'enable',
        'sort_order'
    ];

    protected $casts = [
        'enable' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 활성화된 은행들만 조회
     */
    public function scopeEnabled($query)
    {
        return $query->where('enable', true);
    }

    /**
     * 국가별 은행 조회
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * 정렬 순서로 조회
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * 은행명 검색
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                     ->orWhere('code', 'like', "%{$search}%");
    }

    /**
     * 국가명 반환
     */
    public function getCountryNameAttribute()
    {
        $countries = [
            'KR' => '대한민국',
            'US' => '미국',
            'JP' => '일본',
            'CN' => '중국',
            'GB' => '영국',
            'DE' => '독일',
            'FR' => '프랑스',
            'CA' => '캐나다',
            'AU' => '호주',
            'SG' => '싱가포르',
            'HK' => '홍콩',
            'TH' => '태국',
            'VN' => '베트남',
            'ID' => '인도네시아',
            'MY' => '말레이시아',
            'PH' => '필리핀',
        ];

        return $countries[$this->country] ?? $this->country;
    }

    /**
     * 활성화 상태 텍스트
     */
    public function getStatusTextAttribute()
    {
        return $this->enable ? '활성' : '비활성';
    }

    /**
     * 은행 로고 URL
     */
    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    /**
     * 전화번호 포맷팅
     */
    public function getFormattedPhoneAttribute()
    {
        if (!$this->phone) {
            return null;
        }

        // 한국 전화번호 포맷팅
        if ($this->country === 'KR' && preg_match('/^(\d{4})-(\d{4})$/', $this->phone, $matches)) {
            return $matches[1] . '-' . $matches[2];
        }

        return $this->phone;
    }

    /**
     * 활성화된 은행 목록 조회 (select 옵션용)
     */
    public static function getSelectOptions($country = null)
    {
        $query = static::enabled()->ordered();

        if ($country) {
            $query->byCountry($country);
        }

        return $query->pluck('name', 'id');
    }

    /**
     * 국가별 은행 통계
     */
    public static function getCountryStats()
    {
        return static::selectRaw('country, COUNT(*) as total, SUM(enable) as active')
                     ->groupBy('country')
                     ->get()
                     ->mapWithKeys(function ($item) {
                         return [$item->country => [
                             'total' => $item->total,
                             'active' => $item->active,
                             'inactive' => $item->total - $item->active
                         ]];
                     });
    }
}