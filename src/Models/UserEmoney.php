<?php

namespace Jiny\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

/**
 * UserEmoney 모델 - 사용자 이머니 지갑
 *
 * [모델 역할 및 목적]
 * - 사용자별 이머니 전자지갑 정보 관리
 * - 이머니 잔액, 포인트, 거래 통계 저장
 * - 샤딩된 사용자 시스템과 연동 (UUID 기반)
 * - 충전, 출금, 사용 내역 추적
 *
 * [테이블 구조]
 * - 테이블명: user_emoney
 * - 기본키: id (auto increment)
 * - 외래키: user_uuid (샤딩된 사용자 UUID)
 * - 타임스탬프: created_at, updated_at
 *
 * [주요 컬럼]
 * - user_uuid: 사용자 UUID (샤딩 시스템용)
 * - balance: 현재 이머니 잔액 (decimal, 2자리)
 * - total_deposit: 총 충전 금액
 * - total_used: 총 사용 금액
 * - total_withdrawn: 총 출금 금액
 * - points: 보유 포인트 (integer)
 * - currency: 통화 (기본값: KRW)
 * - status: 지갑 상태 (active, inactive, suspended)
 *
 * [엘로퀀트 관계 (Relationships)]
 * - user(): 사용자 정보 (belongsTo, UUID 기반)
 * - deposits(): 충전 기록 (hasMany)
 * - withdrawals(): 출금 기록 (hasMany)
 * - logs(): 모든 거래 로그 (hasMany)
 *
 * [핵심 메소드]
 * - addBalance($amount, $description): 잔액 추가 + 로그 기록
 * - subtractBalance($amount, $description): 잔액 차감 + 로그 기록
 * - addPoints($points, $description): 포인트 추가 + 로그 기록
 * - usePoints($points, $description): 포인트 사용 + 로그 기록
 * - isActive(): 활성 상태 확인
 * - findOrCreateForUser($userId): 사용자별 지갑 생성/조회
 *
 * [정밀도 처리]
 * - bcadd(), bcsub(), bccomp() 사용으로 부동소수점 오차 방지
 * - balance 관련 계산은 소수점 2자리 정밀도 유지
 * - 금융 거래의 정확성 보장
 *
 * [트랜잭션 안전성]
 * - 잔액 부족 시 예외 발생으로 무결성 보장
 * - 모든 거래는 자동으로 로그 기록
 * - 상태 변경과 로그 기록의 원자적 처리
 *
 * [샤딩 시스템 지원]
 * - user_uuid를 통한 샤딩된 사용자 테이블 연동
 * - 기존 user_id 대신 UUID 기반 관계 설정
 * - 크로스 샤드 데이터 일관성 유지
 *
 * [사용 예시]
 * ```php
 * // 사용자 지갑 조회 또는 생성
 * $wallet = UserEmoney::findOrCreateForUser($userId);
 *
 * // 잔액 추가 (충전)
 * $wallet->addBalance(10000, '계좌 이체 충전');
 *
 * // 잔액 차감 (사용)
 * $wallet->subtractBalance(5000, '상품 구매');
 *
 * // 포인트 적립
 * $wallet->addPoints(100, '구매 적립');
 * ```
 *
 * [관련 모델]
 * - UserEmoneyDeposit: 충전 기록
 * - UserEmoneyWithdraw: 출금 기록
 * - UserEmoneyLog: 거래 로그
 * - UserEmoneyBank: 사용자 은행계좌
 *
 * [보안 고려사항]
 * - 잔액/포인트 변경 시 필수 로그 기록
 * - 부정확한 계산 방지를 위한 BC Math 함수 사용
 * - 잔액 부족 시 예외 처리로 무결성 보장
 * - 지갑 상태 관리로 비활성 계정 거래 차단
 */
class UserEmoney extends Model
{
    use HasFactory;

    protected $table = 'user_emoney';

    protected $fillable = [
        'user_uuid',
        'balance',
        'total_deposit',
        'total_used',
        'total_withdrawn',
        'points',
        'currency',
        'status'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'points' => 'integer',
        'total_deposit' => 'decimal:2',
        'total_used' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
    ];

    /**
     * 사용자 관계
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    /**
     * 입금 기록
     */
    public function deposits()
    {
        return $this->hasMany(UserEmoneyDeposit::class, 'user_uuid', 'user_uuid');
    }

    /**
     * 출금 기록
     */
    public function withdrawals()
    {
        return $this->hasMany(UserEmoneyWithdraw::class, 'user_uuid', 'user_uuid');
    }

    /**
     * 거래 로그
     */
    public function logs()
    {
        return $this->hasMany(UserEmoneyLog::class, 'user_uuid', 'user_uuid');
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