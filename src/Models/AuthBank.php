<?php

namespace Jiny\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * AuthBank 모델 - 관리자 인증 은행 정보
 *
 * [모델 역할 및 목적]
 * - 이머니 시스템에서 지원하는 공식 은행 목록 관리
 * - 관리자가 등록한 인증된 은행 정보 저장
 * - 사용자가 계좌 등록 시 선택 가능한 은행 목록 제공
 * - 국가별 은행 분류 및 관리
 *
 * [테이블 구조]
 * - 테이블명: auth_banks
 * - 기본키: id (auto increment)
 * - 타임스탬프: created_at, updated_at
 *
 * [주요 컬럼]
 * - name: 은행명 (필수)
 * - code: 은행 코드 (선택, 고유값)
 * - country: 국가 코드 (ISO 2자리, 예: KR, US)
 * - swift_code: SWIFT 코드 (국제 송금용)
 * - logo: 은행 로고 이미지 경로
 * - website: 은행 공식 웹사이트
 * - phone: 고객센터 전화번호
 * - account_number: 관리자 계좌번호 (선택)
 * - account_holder: 계좌 소유자명 (선택)
 * - description: 은행 설명
 * - enable: 활성화 상태 (boolean)
 * - sort_order: 정렬 순서
 *
 * [스코프 메소드]
 * - enabled(): 활성화된 은행만 조회
 * - byCountry($country): 특정 국가의 은행만 조회
 * - ordered(): 정렬 순서대로 조회
 * - search($search): 은행명/코드로 검색
 *
 * [액세서 (Accessor)]
 * - country_name: 국가 코드를 국가명으로 변환
 * - status_text: 활성화 상태를 텍스트로 변환
 * - logo_url: 로고 이미지의 전체 URL
 * - formatted_phone: 포맷팅된 전화번호
 *
 * [정적 메소드]
 * - getSelectOptions($country): Select 옵션용 은행 목록
 * - getCountryStats(): 국가별 은행 통계
 *
 * [사용 예시]
 * ```php
 * // 활성화된 한국 은행 목록
 * $koreanBanks = AuthBank::enabled()->byCountry('KR')->ordered()->get();
 *
 * // 은행 검색
 * $searchResults = AuthBank::search('국민')->enabled()->get();
 *
 * // Select 옵션용 데이터
 * $options = AuthBank::getSelectOptions('KR');
 * ```
 *
 * [관련 모델 및 테이블]
 * - UserEmoneyBank: 사용자가 등록한 개인 은행계좌
 * - 이 모델은 사용자 계좌 등록 시 참조되는 마스터 데이터
 *
 * [보안 고려사항]
 * - 관리자만 수정 가능한 마스터 데이터
 * - enable 필드로 활성/비활성 제어
 * - 사용자에게는 활성화된 은행만 노출
 */
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
        'account_number',
        'account_holder',
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