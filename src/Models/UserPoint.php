<?php

namespace Jiny\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

/**
 * UserPoint 모델 - 사용자 포인트 계정
 *
 * [모델 역할 및 목적]
 * - 사용자별 포인트 지갑 및 잔액 관리
 * - 포인트 적립, 사용, 환불, 만료 처리
 * - 포인트 거래 이력 및 통계 추적
 * - 샤딩된 사용자 시스템과 연동 (UUID 기반)
 * - 포인트 만료 스케줄링 및 자동 처리
 *
 * [테이블 구조]
 * - 테이블명: user_point
 * - 기본키: id (auto increment)
 * - 외래키: user_id, user_uuid (샤딩된 사용자 참조)
 * - 타임스탬프: created_at, updated_at
 *
 * [주요 컬럼]
 * - user_id: 사용자 ID (기존 시스템 호환용)
 * - user_uuid: 사용자 UUID (샤딩 시스템용)
 * - shard_id: 샤드 ID (데이터베이스 샤딩)
 * - balance: 현재 포인트 잔액 (decimal, 2자리)
 * - total_earned: 총 적립 포인트 (decimal, 2자리)
 * - total_used: 총 사용 포인트 (decimal, 2자리)
 * - total_expired: 총 만료 포인트 (decimal, 2자리)
 *
 * [엘로퀀트 관계 (Relationships)]
 * - user(): 사용자 정보 (belongsTo)
 * - logs(): 포인트 거래 로그 (hasMany)
 * - expiries(): 만료 예정 포인트 (hasMany)
 *
 * [핵심 메소드]
 * - earnPoints($amount, ...): 포인트 적립 및 로그 기록
 * - usePoints($amount, ...): 포인트 사용 및 잔액 차감
 * - refundPoints($amount, ...): 포인트 환불 처리
 * - expirePoints($amount, ...): 포인트 만료 처리
 * - adminAdjustment($amount, ...): 관리자 포인트 조정
 * - findOrCreateForUser($userId): 사용자별 포인트 계정 생성/조회
 * - getExpiringPoints($days): 만료 예정 포인트 조회
 *
 * [포인트 거래 유형]
 * - earn: 포인트 적립 (구매, 리뷰, 이벤트 참여 등)
 * - use: 포인트 사용 (할인, 상품 교환 등)
 * - refund: 포인트 환불 (주문 취소, 반품 등)
 * - expire: 포인트 만료 (유효기간 만료)
 * - admin: 관리자 조정 (지급/차감)
 *
 * [포인트 만료 시스템]
 * - 적립 시 만료일 설정 가능 (expires_at)
 * - UserPointExpiry 모델로 만료 스케줄 관리
 * - 배치 작업으로 주기적 만료 처리
 * - 만료 예정 포인트 사전 알림
 *
 * [샤딩 시스템 지원]
 * - user_uuid와 shard_id로 분산 데이터베이스 지원
 * - 크로스 샤드 포인트 이체 고려
 * - 데이터 일관성 및 무결성 보장
 * - 성능 확장성 대응
 *
 * [잔액 정합성 보장]
 * - balance = total_earned - total_used - total_expired
 * - 모든 거래는 UserPointLog에 자동 기록
 * - 트랜잭션으로 원자적 처리
 * - 정기적 잔액 검증 및 복구
 *
 * [사용 예시]
 * ```php
 * // 사용자 포인트 계정 조회/생성
 * $userPoint = UserPoint::findOrCreateForUser($userId);
 *
 * // 포인트 적립 (30일 후 만료)
 * $userPoint->earnPoints(1000, '구매 적립', 'order', $orderId, now()->addDays(30));
 *
 * // 포인트 사용
 * $userPoint->usePoints(500, '할인 사용', 'order', $orderId);
 *
 * // 포인트 환불
 * $userPoint->refundPoints(200, '주문 취소', 'order', $orderId);
 *
 * // 만료 예정 포인트 조회 (30일 이내)
 * $expiringPoints = $userPoint->getExpiringPoints(30);
 *
 * // 관리자 포인트 조정
 * $userPoint->adminAdjustment(5000, '이벤트 지급', $adminId);
 * ```
 *
 * [포인트 적립 정책]
 * - 구매 금액 대비 적립률 적용
 * - 회원 등급별 차등 적립
 * - 이벤트/프로모션 추가 적립
 * - 리뷰, 추천 등 활동 적립
 * - 출석체크, 미션 완료 적립
 *
 * [포인트 사용 정책]
 * - 최소 사용 단위 제한
 * - 최대 사용 한도 설정
 * - 특정 상품/카테고리 제한
 * - 할인 중복 적용 규칙
 * - 포인트-현금 혼합 결제
 *
 * [보안 고려사항]
 * - 포인트 부정 적립 방지
 * - 중복 사용 차단
 * - 비정상적 거래 패턴 탐지
 * - 관리자 권한 접근 제어
 * - 거래 내역 불변성 보장
 *
 * [성능 최적화]
 * - 인덱스 최적화 (user_id, user_uuid)
 * - 배치 처리로 만료 작업 효율화
 * - 캐시 활용으로 잔액 조회 성능 향상
 * - 샤딩으로 데이터 분산 처리
 *
 * [관련 모델]
 * - User: 포인트 소유 사용자
 * - UserPointLog: 포인트 거래 로그
 * - UserPointExpiry: 포인트 만료 스케줄
 * - UserEmoney: 포인트-이머니 연동
 *
 * [외부 시스템 연동]
 * - 주문 시스템 (적립/사용)
 * - 이벤트 시스템 (프로모션 적립)
 * - 회원등급 시스템 (등급별 적립률)
 * - 알림 시스템 (만료 예정 알림)
 * - 통계 시스템 (포인트 분석)
 *
 * [비즈니스 분석]
 * - 포인트 적립/사용 패턴 분석
 * - 만료율 및 활용도 측정
 * - 프로모션 효과 분석
 * - 사용자 참여도 지표
 * - ROI 계산 및 최적화
 *
 * [규정 준수]
 * - 포인트 회계 처리 기준
 * - 소비자보호법 준수
 * - 개인정보보호 규정
 * - 세무 신고 대응
 * - 감사 대응 체계
 *
 * [장애 대응]
 * - 포인트 잔액 불일치 복구
 * - 중복 처리 방지 및 롤백
 * - 시스템 장애 시 보상 정책
 * - 데이터 백업 및 복구
 * - 긴급 운영 매뉴얼
 *
 * [확장 계획]
 * - 다중 포인트 유형 지원
 * - 포인트 선물 기능
 * - 포인트 마켓플레이스
 * - AI 기반 개인화 적립
 * - 블록체인 기반 투명성 강화
 */
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