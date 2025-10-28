<?php

namespace Jiny\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

/**
 * UserEmoneyLog 모델 - 이머니 거래 로그
 *
 * [모델 역할 및 목적]
 * - 모든 이머니 관련 거래의 완전한 감사 추적 (Audit Trail)
 * - 입금, 출금, 송금, 구매, 환불 등 모든 거래 이력 기록
 * - 잔액 변동 이력 및 포인트 사용 내역 추적
 * - 규제 준수 및 분쟁 해결을 위한 증빙 자료
 * - 거래 패턴 분석 및 이상 거래 탐지 기반 데이터
 *
 * [테이블 구조]
 * - 테이블명: user_emoney_log
 * - 기본키: id (auto increment)
 * - 외래키: user_id (거래 사용자)
 * - 타임스탬프: created_at, updated_at, processed_at
 *
 * [주요 컬럼]
 * - user_id: 거래 대상 사용자 ID
 * - email: 사용자 이메일 (중복 저장, 변경 이력 추적용)
 * - type: 거래 유형 (deposit, withdraw, transfer, purchase 등)
 * - trans: 거래 고유번호/참조번호
 * - amount: 거래 금액 (decimal, 2자리)
 * - balance: 거래 후 잔액 (decimal, 2자리)
 * - point: 거래 후 포인트 (integer)
 * - fee: 거래 수수료 (decimal, 2자리)
 * - tax: 거래 관련 세금 (decimal, 2자리)
 * - currency: 거래 통화 (KRW, USD, EUR 등)
 * - exchange: 적용 환율 (decimal, 4자리)
 * - description: 거래 설명/메모
 * - reference: 외부 참조번호 (PG사, 은행 거래번호 등)
 * - method: 거래 방법 (bank, card, virtual, mobile 등)
 * - account: 관련 계좌번호
 * - bank: 관련 은행명
 * - status: 거래 상태 (pending, processing, completed, failed, cancelled, refunded)
 * - processed_at: 거래 처리 완료 시간
 * - ip: 거래 발생 IP 주소
 *
 * [엘로퀀트 관계 (Relationships)]
 * - user(): 거래 대상 사용자 (belongsTo)
 * - wallet(): 연결된 이머니 지갑 (belongsTo)
 *
 * [핵심 메소드]
 * - getTypeLabel(): 거래 유형 한글 레이블 반환
 * - getStatusLabel(): 거래 상태 한글 레이블 반환
 * - isSuccessful(): 성공적으로 완료된 거래 여부
 * - isPending(): 대기 중인 거래 여부
 * - isFailed(): 실패한 거래 여부
 *
 * [거래 유형 (type)]
 * - deposit: 입금/충전 (외부 → 지갑)
 * - withdraw: 출금/인출 (지갑 → 외부)
 * - transfer: 송금 (지갑 → 다른 사용자)
 * - receive: 수신 (다른 사용자 → 지갑)
 * - purchase: 구매 (지갑 → 상품/서비스)
 * - refund: 환불 (상품/서비스 → 지갑)
 * - point_add: 포인트 적립 (보상 → 포인트)
 * - point_use: 포인트 사용 (포인트 → 지갑)
 * - point_convert: 포인트 전환 (지갑 ↔ 포인트)
 * - fee: 수수료 차감 (지갑 → 시스템)
 * - adjustment: 관리자 조정 (시스템 ↔ 지갑)
 *
 * [거래 상태 (status)]
 * - pending: 거래 신청/대기 중
 * - processing: 거래 처리 중
 * - completed: 거래 성공 완료
 * - failed: 거래 처리 실패
 * - cancelled: 거래 취소됨
 * - refunded: 거래 환불됨
 *
 * [감사 추적 (Audit Trail)]
 * - 모든 거래의 before/after 상태 기록
 * - 거래 시점의 환율 정보 보존
 * - IP 주소 기록으로 지리적 위치 추적
 * - 외부 참조번호로 타 시스템 연동 추적
 * - 불변(immutable) 로그 - 수정 불가, 생성만 가능
 *
 * [보안 및 규제 준수]
 * - 전자금융거래법 준수 (거래 내역 5년 보관)
 * - 개인정보보호법 준수 (민감정보 암호화 고려)
 * - 자금세탁방지법 준수 (의심거래 모니터링)
 * - 부정거래 탐지 및 패턴 분석 지원
 * - 분쟁 해결을 위한 증빙 자료 제공
 *
 * [데이터 무결성]
 * - 거래 순서 보장 (타임스탬프 기반)
 * - 잔액 일관성 검증 가능
 * - 중복 거래 방지 (trans 필드 활용)
 * - 원자적 거래 보장 (트랜잭션 사용)
 *
 * [성능 최적화]
 * - user_id, type, created_at 인덱스 권장
 * - 대용량 데이터 처리를 위한 파티셔닝 고려
 * - 오래된 로그 아카이빙 정책 필요
 * - 실시간 조회 vs 배치 분석 분리
 *
 * [사용 예시]
 * ```php
 * // 거래 로그 생성
 * UserEmoneyLog::create([
 *     'user_id' => $userId,
 *     'type' => 'deposit',
 *     'amount' => 10000,
 *     'balance' => $newBalance,
 *     'description' => '계좌이체 충전',
 *     'status' => 'completed'
 * ]);
 *
 * // 사용자별 거래 내역 조회
 * $logs = UserEmoneyLog::where('user_id', $userId)
 *     ->where('status', 'completed')
 *     ->orderBy('created_at', 'desc')
 *     ->paginate(20);
 *
 * // 거래 유형별 통계
 * $stats = UserEmoneyLog::selectRaw('type, COUNT(*) as count, SUM(amount) as total')
 *     ->where('status', 'completed')
 *     ->groupBy('type')
 *     ->get();
 *
 * // 특정 기간 거래 분석
 * $analysis = UserEmoneyLog::whereBetween('created_at', [$startDate, $endDate])
 *     ->where('isSuccessful', true)
 *     ->get();
 * ```
 *
 * [관련 모델]
 * - User: 거래 주체 사용자
 * - UserEmoney: 이머니 지갑 (잔액 상태)
 * - UserEmoneyDeposit: 입금 거래 상세
 * - UserEmoneyWithdraw: 출금 거래 상세
 * - UserPoint: 포인트 관련 거래
 *
 * [분석 및 리포팅]
 * - 일/월/년별 거래량 통계
 * - 사용자별 거래 패턴 분석
 * - 거래 방법별 선호도 분석
 * - 실패 거래 원인 분석
 * - 수수료 수익 분석
 * - 환율 변동 영향 분석
 *
 * [이상 거래 탐지]
 * - 단시간 대량 거래 패턴
 * - 비정상적인 IP 변경
 * - 계좌 정보 빈번한 변경
 * - 특정 금액대 반복 거래
 * - 심야/새벽 시간대 이상 거래
 *
 * [데이터 아카이빙]
 * - 정기적인 오래된 로그 아카이빙
 * - 규제 요구사항에 따른 보관 기간 준수
 * - 압축 저장으로 스토리지 최적화
 * - 백업 및 재해복구 계획 수립
 *
 * [API 연동 지원]
 * - REST API를 통한 거래 내역 제공
 * - 실시간 거래 알림 웹훅
 * - 외부 회계 시스템 연동
 * - 세무 신고 데이터 추출
 *
 * [모니터링 및 알림]
 * - 실시간 거래 모니터링
 * - 임계값 초과 시 알림
 * - 시스템 장애 감지
 * - 성능 지표 추적
 */
class UserEmoneyLog extends Model
{
    use HasFactory;

    protected $table = 'user_emoney_log';

    protected $fillable = [
        'user_id',
        'email',
        'type',
        'trans',
        'amount',
        'balance',
        'point',
        'fee',
        'tax',
        'currency',
        'exchange',
        'description',
        'reference',
        'method',
        'account',
        'bank',
        'status',
        'processed_at',
        'ip'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'exchange' => 'decimal:4',
        'point' => 'integer',
        'processed_at' => 'datetime',
    ];

    /**
     * 사용자 관계
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 전자지갑 관계
     */
    public function wallet()
    {
        return $this->belongsTo(UserEmoney::class, 'user_id', 'user_id');
    }

    /**
     * 거래 타입 레이블
     */
    public function getTypeLabel()
    {
        $types = [
            'deposit' => '입금',
            'withdraw' => '출금',
            'transfer' => '송금',
            'receive' => '수신',
            'purchase' => '구매',
            'refund' => '환불',
            'point_add' => '포인트 적립',
            'point_use' => '포인트 사용',
            'point_convert' => '포인트 전환',
            'fee' => '수수료',
            'adjustment' => '조정',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * 상태 레이블
     */
    public function getStatusLabel()
    {
        $statuses = [
            'pending' => '대기중',
            'processing' => '처리중',
            'completed' => '완료',
            'failed' => '실패',
            'cancelled' => '취소',
            'refunded' => '환불됨',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * 성공 여부
     */
    public function isSuccessful()
    {
        return $this->status === 'completed';
    }

    /**
     * 대기 중 여부
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * 실패 여부
     */
    public function isFailed()
    {
        return in_array($this->status, ['failed', 'cancelled']);
    }
}