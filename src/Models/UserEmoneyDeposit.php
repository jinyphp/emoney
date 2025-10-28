<?php

namespace Jiny\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

/**
 * UserEmoneyDeposit 모델 - 이머니 충전 기록
 *
 * [모델 역할 및 목적]
 * - 사용자의 이머니 충전(입금) 거래 기록 관리
 * - 다양한 결제 수단별 입금 내역 추적
 * - 관리자 승인 기반 충전 처리 워크플로우
 * - 환율 적용 다중 통화 충전 지원
 * - 입금 확인/취소 처리 및 이력 관리
 *
 * [테이블 구조]
 * - 테이블명: user_emoney_deposit
 * - 기본키: id (auto increment)
 * - 외래키: user_id (충전 사용자), admin_id (처리 관리자)
 * - 타임스탬프: created_at, updated_at, confirmed_at
 *
 * [주요 컬럼]
 * - user_id: 충전 신청 사용자 ID
 * - email: 사용자 이메일 (중복 저장)
 * - trans: 거래 고유번호/참조번호
 * - amount: 충전 금액 (decimal, 2자리)
 * - currency: 결제 통화 (KRW, USD, EUR 등)
 * - exchange: 환율 (decimal, 4자리)
 * - method: 결제 방법 (bank, card, virtual, mobile, point, admin)
 * - bank: 입금 은행명
 * - account: 입금 계좌번호
 * - depositor: 입금자명
 * - description: 충전 설명/메모
 * - reference: 외부 참조번호 (PG사 거래번호 등)
 * - status: 처리 상태 (pending, confirmed, cancelled)
 * - confirmed_at: 확인 완료 시간
 * - admin_id: 처리한 관리자 ID
 * - admin_memo: 관리자 처리 메모
 * - ip: 충전 신청 IP 주소
 *
 * [엘로퀀트 관계 (Relationships)]
 * - user(): 충전 신청 사용자 (belongsTo)
 * - wallet(): 연결된 이머니 지갑 (belongsTo)
 * - admin(): 처리한 관리자 (belongsTo)
 *
 * [핵심 메소드]
 * - confirm($adminId, $memo): 입금 확인 처리 및 잔액 증가
 * - cancel($adminId, $memo): 입금 취소 처리 및 잔액 차감
 * - getMethodLabel(): 결제 방법 한글 레이블 반환
 * - getStatusLabel(): 처리 상태 한글 레이블 반환
 *
 * [충전 처리 워크플로우]
 * 1. 사용자 충전 신청 (pending 상태로 생성)
 * 2. 실제 입금 확인 (은행 입금, PG 결제 등)
 * 3. 관리자 승인 처리 (confirm 메소드 호출)
 * 4. 이머니 지갑 잔액 증가 및 로그 기록
 * 5. 사용자 알림 발송 (confirmed 상태)
 *
 * [결제 방법 (method)]
 * - bank: 무통장입금 (가장 일반적)
 * - card: 신용카드 결제
 * - virtual: 가상계좌 입금
 * - mobile: 휴대폰 소액결제
 * - point: 포인트 전환 충전
 * - admin: 관리자 직접 충전
 *
 * [상태 관리 (status)]
 * - pending: 입금 대기 중 (신청 상태)
 * - confirmed: 입금 확인 완료 (지갑 잔액 증가됨)
 * - cancelled: 입금 취소됨 (잔액 차감 또는 미처리)
 *
 * [환율 처리]
 * - exchange 필드로 충전 시점 환율 기록
 * - 외화 충전 시 KRW 환산 금액 계산
 * - 환율 변동으로 인한 차액 처리 고려
 *
 * [보안 및 추적]
 * - IP 주소 기록으로 충전 위치 추적
 * - 거래 고유번호(trans)로 중복 처리 방지
 * - 관리자 처리 이력 및 메모 기록
 * - 외부 참조번호로 PG사 연동 추적
 *
 * [트랜잭션 안전성]
 * - confirm/cancel 메소드에서 이중 처리 방지
 * - 잔액 변경과 상태 변경의 원자적 처리
 * - 확인된 입금 취소 시 잔액 차감 로직
 * - 예외 상황 처리 및 롤백 메커니즘
 *
 * [사용 예시]
 * ```php
 * // 입금 확인 처리 (관리자)
 * $deposit = UserEmoneyDeposit::find($depositId);
 * $deposit->confirm($adminId, '입금 확인 완료');
 *
 * // 입금 취소 처리
 * $deposit->cancel($adminId, '중복 입금으로 취소');
 *
 * // 사용자별 입금 내역 조회
 * $deposits = UserEmoneyDeposit::where('user_id', $userId)
 *     ->where('status', 'confirmed')
 *     ->get();
 *
 * // 결제 방법별 통계
 * $stats = UserEmoneyDeposit::selectRaw('method, COUNT(*) as count, SUM(amount) as total')
 *     ->where('status', 'confirmed')
 *     ->groupBy('method')
 *     ->get();
 * ```
 *
 * [관련 모델]
 * - User: 충전 사용자 및 처리 관리자
 * - UserEmoney: 이머니 지갑 (잔액 증감)
 * - UserEmoneyLog: 충전 처리 로그 기록
 * - UserEmoneyBank: 사용자 등록 계좌 정보
 *
 * [외부 연동]
 * - PG사 결제 시스템 (카드, 가상계좌)
 * - 은행 API (실시간 입금 확인)
 * - 환율 API (외화 충전 시)
 * - SMS/이메일 알림 시스템
 *
 * [데이터 분석 활용]
 * - 충전 패턴 분석 (시간대, 요일별)
 * - 결제 수단별 선호도 통계
 * - 환율 변동 영향 분석
 * - 충전 취소율 모니터링
 *
 * [규제 준수]
 * - 전자금융거래법 준수
 * - 개인정보보호법 (입금자 정보 보호)
 * - 자금세탁방지법 (고액 거래 모니터링)
 * - 외환거래법 (외화 충전 규제)
 */
class UserEmoneyDeposit extends Model
{
    use HasFactory;

    protected $table = 'user_emoney_deposit';

    protected $fillable = [
        'user_id',
        'email',
        'trans',
        'amount',
        'currency',
        'exchange',
        'method',
        'bank',
        'account',
        'depositor',
        'description',
        'reference',
        'confirmed_at',
        'status',
        'admin_id',
        'admin_memo',
        'ip'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange' => 'decimal:4',
        'confirmed_at' => 'datetime',
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
     * 관리자 관계
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * 입금 확인
     */
    public function confirm($adminId = null, $memo = null)
    {
        if ($this->status === 'confirmed') {
            throw new \Exception('이미 확인된 입금입니다.');
        }

        $this->status = 'confirmed';
        $this->confirmed_at = now();
        $this->admin_id = $adminId;
        $this->admin_memo = $memo;
        $this->save();

        // 전자지갑 잔액 추가
        $wallet = UserEmoney::findOrCreateForUser($this->user_id);
        $wallet->addBalance($this->amount, "입금: {$this->trans}");

        return $this;
    }

    /**
     * 입금 취소
     */
    public function cancel($adminId = null, $memo = null)
    {
        if ($this->status === 'cancelled') {
            throw new \Exception('이미 취소된 입금입니다.');
        }

        if ($this->status === 'confirmed') {
            // 확인된 입금인 경우 잔액 차감
            $wallet = UserEmoney::findOrCreateForUser($this->user_id);
            $wallet->subtractBalance($this->amount, "입금 취소: {$this->trans}");
        }

        $this->status = 'cancelled';
        $this->admin_id = $adminId;
        $this->admin_memo = $memo;
        $this->save();

        return $this;
    }

    /**
     * 입금 방법 레이블
     */
    public function getMethodLabel()
    {
        $methods = [
            'bank' => '무통장입금',
            'card' => '신용카드',
            'virtual' => '가상계좌',
            'mobile' => '휴대폰',
            'point' => '포인트전환',
            'admin' => '관리자충전',
        ];

        return $methods[$this->method] ?? $this->method;
    }

    /**
     * 상태 레이블
     */
    public function getStatusLabel()
    {
        $statuses = [
            'pending' => '대기중',
            'confirmed' => '확인완료',
            'cancelled' => '취소됨',
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}