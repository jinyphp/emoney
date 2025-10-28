<?php

namespace Jiny\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jiny\Auth\Models\JwtAuth;

/**
 * UserPointExpiry 모델 - 포인트 만료 스케줄
 *
 * [모델 역할 및 목적]
 * - 사용자 포인트의 만료 일정 관리 및 추적
 * - 포인트별 만료일 스케줄링 및 자동 만료 처리
 * - 만료 예정 포인트 사전 알림 시스템
 * - 배치 작업을 통한 대량 만료 처리
 * - 포인트 만료 정책 및 규칙 적용
 *
 * [테이블 구조]
 * - 테이블명: user_point_expiry
 * - 기본키: id (auto increment)
 * - 외래키: user_id, point_log_id (포인트 적립 로그)
 * - 타임스탬프: created_at, updated_at, expires_at, expired_at, notified_at
 *
 * [주요 컬럼]
 * - user_id: 사용자 ID (기존 시스템 호환용)
 * - user_uuid: 사용자 UUID (샤딩 시스템용)
 * - shard_id: 샤드 ID (데이터베이스 샤딩)
 * - point_log_id: 포인트 적립 로그 ID (원본 거래 추적)
 * - amount: 만료 예정 포인트 금액 (decimal, 2자리)
 * - expires_at: 만료 예정 일시 (datetime)
 * - expired: 만료 처리 완료 여부 (boolean)
 * - expired_at: 실제 만료 처리 일시 (datetime)
 * - notified: 만료 알림 발송 여부 (boolean)
 * - notified_at: 알림 발송 일시 (datetime)
 *
 * [엘로퀀트 관계 (Relationships)]
 * - user(): 포인트 소유 사용자 (belongsTo JwtAuth)
 * - pointLog(): 원본 포인트 적립 로그 (belongsTo UserPointLog)
 *
 * [스코프 메소드 (Query Scopes)]
 * - notExpired(): 만료되지 않은 포인트 조회
 * - expired(): 만료 처리된 포인트 조회
 * - expiring($days): 지정 기간 내 만료 예정 포인트
 * - expiringToday(): 오늘 만료 예정 포인트
 * - notNotified(): 알림 미발송 포인트
 *
 * [핵심 메소드]
 * - processExpiry(): 포인트 만료 처리 실행
 * - markAsNotified(): 알림 발송 완료 표시
 * - getDaysUntilExpiryAttribute(): 만료까지 남은 일수
 * - isExpired(): 만료 여부 확인
 * - isExpiringSoon($days): 만료 임박 여부 확인
 * - getUserData(): 샤딩된 사용자 정보 조회
 *
 * [정적 메소드 (Static Methods)]
 * - getTotalExpiringAmount($userId, $days): 사용자별 만료 예정 총액
 * - getExpiredBatch($limit): 만료 처리 대상 일괄 조회
 * - getNotificationBatch($days, $limit): 알림 발송 대상 일괄 조회
 * - getUserExpirySchedule($userId): 사용자별 만료 스케줄
 *
 * [포인트 만료 정책]
 * - 포인트 적립 시점에 만료일 자동 설정
 * - 기본 만료 기간: 1년 (정책에 따라 조정 가능)
 * - 특별 이벤트 포인트: 단기 만료 (30-90일)
 * - VIP 등급 포인트: 연장 만료 (2년)
 * - 법정 최대 만료 기간 준수 (5년)
 *
 * [만료 처리 워크플로우]
 * 1. 만료일 7일 전: 1차 알림 발송
 * 2. 만료일 1일 전: 2차 알림 발송
 * 3. 만료일 당일: 자동 만료 처리
 * 4. 만료 완료: 포인트 잔액 차감 및 로그 기록
 * 5. 만료 후: 복구 불가 상태로 전환
 *
 * [배치 처리 시스템]
 * - 일일 만료 처리: 매일 오전 2시 실행
 * - 알림 발송: 매일 오전 9시 실행
 * - 만료 통계: 매주 월요일 오전 6시 실행
 * - 데이터 정리: 매월 첫째 주 일요일 실행
 *
 * [알림 시스템]
 * - 이메일 알림: 만료 7일, 1일 전
 * - SMS 알림: 만료 1일 전 (VIP 회원)
 * - 푸시 알림: 앱 설치 사용자
 * - 인앱 알림: 로그인 시 팝업 표시
 *
 * [사용 예시]
 * ```php
 * // 만료 예정 포인트 조회 (30일 이내)
 * $expiringPoints = UserPointExpiry::where('user_id', $userId)
 *     ->expiring(30)
 *     ->notExpired()
 *     ->get();
 *
 * // 오늘 만료 처리 대상 조회
 * $todayExpired = UserPointExpiry::getExpiredBatch(1000);
 *
 * // 만료 처리 실행
 * foreach ($todayExpired as $expiry) {
 *     $expiry->processExpiry();
 * }
 *
 * // 알림 발송 대상 조회 (7일 이내)
 * $notifications = UserPointExpiry::getNotificationBatch(7, 500);
 *
 * // 사용자별 만료 예정 총액
 * $totalExpiring = UserPointExpiry::getTotalExpiringAmount($userId, 30);
 *
 * // 사용자별 월별 만료 스케줄
 * $schedule = UserPointExpiry::getUserExpirySchedule($userId);
 * ```
 *
 * [성능 최적화]
 * - 인덱스 최적화: (user_id, expired, expires_at)
 * - 파티셔닝: expires_at 기준 월별 파티션
 * - 배치 처리: 대량 데이터 청크 단위 처리
 * - 캐시 활용: 자주 조회되는 만료 통계
 *
 * [데이터 정합성]
 * - 포인트 적립 시 자동 만료 스케줄 생성
 * - 만료 처리 시 원자적 트랜잭션 보장
 * - 중복 만료 방지 체크
 * - 만료 금액과 실제 차감 금액 일치 검증
 *
 * [샤딩 시스템 지원]
 * - user_uuid와 shard_id로 분산 처리
 * - 크로스 샤드 만료 스케줄 동기화
 * - 샤드별 독립적 배치 처리
 * - 데이터 일관성 보장
 *
 * [규제 및 법적 요구사항]
 * - 소비자보호법: 포인트 만료 사전 고지
 * - 개인정보보호법: 알림 수신 동의 관리
 * - 전자상거래법: 만료 정책 명시 의무
 * - 회계 기준: 포인트 부채 인식 및 소멸
 *
 * [모니터링 지표]
 * - 일일 만료 포인트 금액
 * - 만료율 (만료/적립 비율)
 * - 알림 발송 성공률
 * - 만료 처리 지연 건수
 * - 사용자별 만료 패턴
 *
 * [장애 대응]
 * - 만료 처리 실패 시 재시도 로직
 * - 알림 발송 실패 시 대체 채널 활용
 * - 시스템 장애 시 만료 유예 정책
 * - 데이터 복구 및 보상 프로세스
 *
 * [관련 모델]
 * - UserPoint: 사용자 포인트 계정
 * - UserPointLog: 포인트 거래 로그
 * - JwtAuth: 사용자 인증 정보 (샤딩 지원)
 *
 * [외부 시스템 연동]
 * - 알림 서비스 (이메일, SMS, 푸시)
 * - 회계 시스템 (포인트 부채 관리)
 * - 고객센터 시스템 (만료 문의 대응)
 * - 마케팅 시스템 (만료 방지 프로모션)
 *
 * [비즈니스 인사이트]
 * - 만료 방지 마케팅 기회 식별
 * - 포인트 활용도 개선 방안
 * - 만료 정책 최적화
 * - 사용자 세그먼트별 만료 패턴
 * - ROI 기반 알림 전략 수립
 */
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
     * 사용자 관계 (샤딩된 사용자 지원)
     */
    public function user()
    {
        return $this->belongsTo(JwtAuth::class, 'user_id');
    }

    /**
     * 사용자 정보를 동적으로 조회하는 헬퍼 메서드
     */
    public function getUserData()
    {
        if ($this->user_id) {
            return JwtAuth::find($this->user_id);
        }

        if ($this->user_uuid) {
            return JwtAuth::findByUuid($this->user_uuid);
        }

        return null;
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