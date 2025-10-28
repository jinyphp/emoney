<?php

namespace Jiny\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

/**
 * UserPointLog 모델 - 포인트 거래 로그
 *
 * [모델 역할 및 목적]
 * - 모든 포인트 관련 거래의 완전한 감사 추적 (Audit Trail)
 * - 포인트 적립, 사용, 환불, 만료 등 모든 거래 이력 기록
 * - 거래 전후 잔액 상태 및 변동 내역 추적
 * - 외부 시스템 연동 거래의 참조 정보 관리
 * - 포인트 거래 패턴 분석 및 이상 거래 탐지 지원
 *
 * [테이블 구조]
 * - 테이블명: user_point_log
 * - 기본키: id (auto increment)
 * - 외래키: user_id, admin_id (거래 실행자)
 * - 타임스탬프: created_at, updated_at, expires_at
 *
 * [주요 컬럼]
 * - user_id: 거래 대상 사용자 ID (기존 시스템 호환용)
 * - user_uuid: 사용자 UUID (샤딩 시스템용)
 * - shard_id: 사용자 샤드 ID (데이터베이스 샤딩)
 * - transaction_type: 거래 유형 (earn, use, refund, expire, admin)
 * - amount: 거래 금액 (decimal, 2자리, 양수/음수 구분)
 * - balance_before: 거래 전 잔액 (decimal, 2자리)
 * - balance_after: 거래 후 잔액 (decimal, 2자리)
 * - reason: 거래 사유/설명
 * - reference_type: 외부 참조 유형 (order, event, promotion 등)
 * - reference_id: 외부 참조 ID
 * - expires_at: 적립 포인트 만료일 (earn 거래만 해당)
 * - admin_id: 관리자 거래 시 실행자 ID
 * - admin_uuid: 관리자 UUID (샤딩 시스템용)
 * - admin_shard_id: 관리자 샤드 ID
 * - metadata: 추가 거래 정보 (JSON)
 *
 * [엘로퀀트 관계 (Relationships)]
 * - user(): 거래 대상 사용자 (belongsTo)
 * - admin(): 거래 실행 관리자 (belongsTo)
 * - expiry(): 연결된 만료 스케줄 (hasOne UserPointExpiry)
 *
 * [스코프 메소드 (Query Scopes)]
 * - byType($type): 거래 유형별 조회
 * - earned(): 적립 거래만 조회
 * - used(): 사용 거래만 조회
 * - refunded(): 환불 거래만 조회
 * - expired(): 만료 거래만 조회
 * - adminAdjustment(): 관리자 조정 거래만 조회
 * - byReference($type, $id): 참조 유형/ID별 조회
 * - dateRange($from, $to): 기간별 조회
 *
 * [핵심 메소드]
 * - getTransactionTypeNameAttribute(): 거래 유형 한글명
 * - isPositive(): 양수 금액 여부 확인
 * - isNegative(): 음수 금액 여부 확인
 * - canExpire(): 만료 가능 거래 여부 확인
 * - getUsageStats($userId, $period): 사용자별 포인트 통계
 * - getByReference($type, $id): 참조별 거래 내역 조회
 *
 * [거래 유형 (transaction_type)]
 * - earn: 포인트 적립 (구매, 리뷰, 이벤트 등)
 * - use: 포인트 사용 (할인, 상품 교환 등)
 * - refund: 포인트 환불 (주문 취소, 반품 등)
 * - expire: 포인트 만료 (유효기간 만료)
 * - admin: 관리자 조정 (수동 지급/차감)
 *
 * [참조 유형 (reference_type)]
 * - order: 주문 관련 거래
 * - event: 이벤트 참여 적립
 * - promotion: 프로모션 적립
 * - review: 리뷰 작성 적립
 * - referral: 추천인 적립
 * - attendance: 출석체크 적립
 * - mission: 미션 완료 적립
 * - compensation: 보상 지급
 *
 * [금액 표기 규칙]
 * - 적립(earn): 양수 금액 (+1000)
 * - 사용(use): 음수 금액 (-500)
 * - 환불(refund): 양수 금액 (+200)
 * - 만료(expire): 음수 금액 (-100)
 * - 관리자 조정: 상황에 따라 양수/음수
 *
 * [메타데이터 활용]
 * - 거래 상세 정보 저장 (JSON 형태)
 * - API 호출 정보, IP 주소 등
 * - 프로모션 코드, 이벤트 ID 등
 * - 외부 시스템 연동 데이터
 * - 확장 가능한 구조로 설계
 *
 * [샤딩 시스템 지원]
 * - user_uuid, shard_id로 사용자 분산 처리
 * - admin_uuid, admin_shard_id로 관리자 분산 처리
 * - 크로스 샤드 거래 추적 가능
 * - 데이터 일관성 및 무결성 보장
 *
 * [사용 예시]
 * ```php
 * // 사용자별 포인트 거래 내역 조회
 * $logs = UserPointLog::where('user_id', $userId)
 *     ->orderBy('created_at', 'desc')
 *     ->paginate(20);
 *
 * // 특정 기간 적립 포인트 조회
 * $earnedPoints = UserPointLog::earned()
 *     ->where('user_id', $userId)
 *     ->dateRange($startDate, $endDate)
 *     ->sum('amount');
 *
 * // 주문별 포인트 내역 조회
 * $orderPoints = UserPointLog::getByReference('order', $orderId);
 *
 * // 사용자 포인트 사용 통계 (1개월)
 * $stats = UserPointLog::getUsageStats($userId, '1month');
 *
 * // 관리자 조정 내역 조회
 * $adjustments = UserPointLog::adminAdjustment()
 *     ->where('admin_id', $adminId)
 *     ->dateRange($startDate, $endDate)
 *     ->get();
 * ```
 *
 * [감사 추적 (Audit Trail)]
 * - 모든 거래의 before/after 잔액 기록
 * - 거래 시점의 만료일 정보 보존
 * - 외부 참조를 통한 원본 거래 추적
 * - 관리자 개입 거래의 실행자 기록
 * - 불변(immutable) 로그 - 수정 불가, 생성만 가능
 *
 * [데이터 무결성]
 * - 거래 순서 보장 (타임스탬프 기반)
 * - 잔액 일관성 검증 가능
 * - 중복 거래 방지 (참조 정보 활용)
 * - 원자적 거래 보장 (트랜잭션 사용)
 *
 * [성능 최적화]
 * - user_id, transaction_type, created_at 복합 인덱스
 * - reference_type, reference_id 복합 인덱스
 * - 대용량 데이터 처리를 위한 파티셔닝 고려
 * - 오래된 로그 아카이빙 정책 수립
 *
 * [분석 및 리포팅]
 * - 일/월/년별 포인트 거래량 통계
 * - 사용자별 포인트 활용 패턴 분석
 * - 거래 유형별 분포 및 트렌드 분석
 * - 적립원별 효과 분석 (주문, 이벤트, 프로모션)
 * - 만료율 및 활용도 측정
 *
 * [이상 거래 탐지]
 * - 단시간 대량 거래 패턴
 * - 비정상적인 적립/사용 비율
 * - 특정 금액대 반복 거래
 * - 관리자 조정 빈도 이상
 * - 외부 참조 없는 거래 패턴
 *
 * [규제 및 컴플라이언스]
 * - 소비자보호법: 포인트 거래 내역 투명 공개
 * - 개인정보보호법: 거래 정보 보호 및 암호화
 * - 전자상거래법: 거래 기록 보관 의무
 * - 회계 기준: 포인트 부채 변동 추적
 * - 세무 신고: 포인트 관련 소득 신고 지원
 *
 * [관련 모델]
 * - UserPoint: 사용자 포인트 계정 (잔액 상태)
 * - UserPointExpiry: 포인트 만료 스케줄
 * - User: 거래 주체 사용자 및 관리자
 *
 * [외부 시스템 연동]
 * - 주문 시스템: 구매 적립/사용 연동
 * - 이벤트 시스템: 참여 적립 연동
 * - 마케팅 시스템: 프로모션 적립 연동
 * - 회계 시스템: 포인트 부채 관리 연동
 * - BI 시스템: 포인트 분석 데이터 제공
 *
 * [모니터링 대상]
 * - 실시간 거래량 모니터링
 * - 거래 실패/오류 알림
 * - 이상 거래 패턴 감지
 * - 시스템 성능 지표 추적
 * - 데이터 일관성 검증
 *
 * [백업 및 아카이빙]
 * - 정기적인 로그 데이터 백업
 * - 오래된 로그 아카이브 처리
 * - 규제 요구사항에 따른 보관 기간 준수
 * - 재해복구 시나리오 대응
 * - 압축 저장으로 스토리지 최적화
 *
 * [확장 계획]
 * - 실시간 스트리밍 로그 처리
 * - 머신러닝 기반 이상 거래 탐지
 * - 다중 포인트 유형 지원
 * - 블록체인 기반 투명성 강화
 * - AI 기반 포인트 사용 패턴 분석
 */
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