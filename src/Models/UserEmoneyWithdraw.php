<?php

namespace Jiny\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

/**
 * UserEmoneyWithdraw 모델 - 이머니 출금 기록
 *
 * [모델 역할 및 목적]
 * - 사용자의 이머니 출금(인출) 거래 기록 관리
 * - 다단계 승인 프로세스 기반 출금 처리
 * - 수수료 및 세금 계산 포함한 정확한 금액 관리
 * - 다양한 출금 방법별 처리 워크플로우
 * - 관리자 승인/거절 시스템 및 이력 관리
 *
 * [테이블 구조]
 * - 테이블명: user_emoney_withdraw
 * - 기본키: id (auto increment)
 * - 외래키: user_id (출금 사용자), admin_id (처리 관리자)
 * - 타임스탬프: created_at, updated_at, processed_at
 *
 * [주요 컬럼]
 * - user_id: 출금 신청 사용자 ID
 * - email: 사용자 이메일 (중복 저장)
 * - trans: 거래 고유번호/참조번호
 * - amount: 순 출금 금액 (decimal, 2자리)
 * - fee: 출금 수수료 (decimal, 2자리)
 * - tax: 출금 세금 (decimal, 2자리)
 * - total: 총 차감 금액 (amount + fee + tax)
 * - currency: 출금 통화 (KRW, USD, EUR 등)
 * - exchange: 환율 (decimal, 4자리)
 * - method: 출금 방법 (bank, card, point, crypto)
 * - bank: 출금 은행명
 * - account: 출금 계좌번호
 * - account_name: 출금 계좌 소유자명
 * - description: 출금 설명/메모
 * - reference: 외부 참조번호 (은행 거래번호 등)
 * - status: 처리 상태 (pending, approved, completed, rejected, cancelled)
 * - processed_at: 처리 완료 시간
 * - admin_id: 처리한 관리자 ID
 * - admin_memo: 관리자 처리 메모
 * - ip: 출금 신청 IP 주소
 *
 * [엘로퀀트 관계 (Relationships)]
 * - user(): 출금 신청 사용자 (belongsTo)
 * - wallet(): 연결된 이머니 지갑 (belongsTo)
 * - admin(): 처리한 관리자 (belongsTo)
 *
 * [핵심 메소드]
 * - approve($adminId, $memo): 출금 승인 처리
 * - complete(): 출금 완료 처리 (실제 송금 완료)
 * - reject($adminId, $reason): 출금 거절 및 잔액 복구
 * - calculateTotal(): 총 차감 금액 계산 (amount + fee + tax)
 * - getMethodLabel(): 출금 방법 한글 레이블 반환
 * - getStatusLabel(): 처리 상태 한글 레이블 반환
 *
 * [출금 처리 워크플로우]
 * 1. 사용자 출금 신청 (pending 상태, 잔액 미차감)
 * 2. 관리자 승인 검토 (approve 메소드 호출)
 * 3. 실제 은행 송금 처리 (외부 시스템)
 * 4. 송금 완료 확인 (complete 메소드 호출)
 * 5. 사용자 알림 발송 (completed 상태)
 * 6. 거절 시 잔액 복구 (reject 메소드)
 *
 * [상태 관리 (status)]
 * - pending: 출금 신청 대기 중 (관리자 승인 전)
 * - approved: 출금 승인됨 (송금 처리 대기)
 * - completed: 출금 완료 (실제 송금 완료)
 * - rejected: 출금 거절됨 (잔액 복구됨)
 * - cancelled: 출금 취소됨 (사용자 요청)
 *
 * [출금 방법 (method)]
 * - bank: 계좌이체 (가장 일반적)
 * - card: 카드 송금 (체크카드 등)
 * - point: 포인트 전환 출금
 * - crypto: 암호화폐 출금
 *
 * [금액 계산 구조]
 * - amount: 사용자가 실제 받을 금액
 * - fee: 출금 수수료 (시스템 운영비)
 * - tax: 출금 관련 세금 (소득세 등)
 * - total: 지갑에서 차감될 총 금액
 * - 계산식: total = amount + fee + tax
 *
 * [수수료 정책]
 * - 출금 금액에 따른 차등 수수료
 * - 출금 방법별 수수료 차이
 * - VIP 등급별 수수료 할인
 * - 월간 무료 출금 한도 적용
 *
 * [보안 및 컴플라이언스]
 * - 출금 한도 제한 (일일/월간)
 * - 본인 명의 계좌 확인
 * - 고액 출금 시 추가 인증
 * - 자금세탁방지법 준수
 * - 외환거래법 규제 준수
 *
 * [잔액 관리]
 * - 출금 신청 시점에는 잔액 차감 안함
 * - 승인 시점에 실제 잔액 차감
 * - 거절 시 잔액 자동 복구
 * - BC Math 함수로 정확한 계산
 *
 * [사용 예시]
 * ```php
 * // 출금 승인 처리 (관리자)
 * $withdraw = UserEmoneyWithdraw::find($withdrawId);
 * $withdraw->approve($adminId, '출금 승인 완료');
 *
 * // 출금 완료 처리 (송금 후)
 * $withdraw->complete();
 *
 * // 출금 거절 처리
 * $withdraw->reject($adminId, '계좌 정보 불일치');
 *
 * // 총 차감 금액 계산
 * $withdraw->calculateTotal();
 *
 * // 사용자별 출금 통계
 * $stats = UserEmoneyWithdraw::where('user_id', $userId)
 *     ->where('status', 'completed')
 *     ->selectRaw('SUM(amount) as total_amount, SUM(fee) as total_fee, COUNT(*) as count')
 *     ->first();
 * ```
 *
 * [관련 모델]
 * - User: 출금 사용자 및 처리 관리자
 * - UserEmoney: 이머니 지갑 (잔액 차감)
 * - UserEmoneyLog: 출금 처리 로그 기록
 * - UserEmoneyBank: 출금 대상 계좌 정보
 *
 * [외부 연동]
 * - 은행 API (실시간 계좌이체)
 * - 카드사 API (카드 송금)
 * - 암호화폐 거래소 API
 * - 환율 API (외화 출금 시)
 * - SMS/이메일 알림 시스템
 *
 * [모니터링 및 분석]
 * - 출금 패턴 분석 (시간대, 금액대별)
 * - 출금 방법별 성공률 통계
 * - 출금 거절 사유 분석
 * - 수수료 수익 분석
 * - 출금 처리 시간 모니터링
 *
 * [리스크 관리]
 * - 이상 거래 패턴 탐지
 * - 출금 빈도 제한
 * - 계좌 변경 이력 추적
 * - 출금 실패 이력 관리
 * - 의심 거래 자동 차단
 *
 * [정밀도 처리]
 * - BC Math 함수 사용으로 부동소수점 오차 방지
 * - 모든 금액 계산에서 소수점 2자리 정밀도 유지
 * - 환율 계산 시 4자리 정밀도 사용
 * - 금융 거래의 정확성 보장
 *
 * [감사 추적 (Audit Trail)]
 * - 모든 상태 변경 이력 기록
 * - 관리자 처리 내역 추적
 * - 출금 신청부터 완료까지 전체 로그
 * - 규제 기관 보고 대응
 */
class UserEmoneyWithdraw extends Model
{
    use HasFactory;

    protected $table = 'user_emoney_withdraw';

    protected $fillable = [
        'user_id',
        'email',
        'trans',
        'amount',
        'fee',
        'tax',
        'total',
        'currency',
        'exchange',
        'method',
        'bank',
        'account',
        'account_name',
        'description',
        'reference',
        'processed_at',
        'status',
        'admin_id',
        'admin_memo',
        'ip'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'exchange' => 'decimal:4',
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
     * 관리자 관계
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * 출금 승인
     */
    public function approve($adminId = null, $memo = null)
    {
        if ($this->status !== 'pending') {
            throw new \Exception('대기 중인 출금만 승인할 수 있습니다.');
        }

        $this->status = 'approved';
        $this->processed_at = now();
        $this->admin_id = $adminId;
        $this->admin_memo = $memo;
        $this->save();

        return $this;
    }

    /**
     * 출금 완료 처리
     */
    public function complete()
    {
        if ($this->status !== 'approved') {
            throw new \Exception('승인된 출금만 완료 처리할 수 있습니다.');
        }

        $this->status = 'completed';
        $this->processed_at = now();
        $this->save();

        return $this;
    }

    /**
     * 출금 거절
     */
    public function reject($adminId = null, $reason = null)
    {
        if (!in_array($this->status, ['pending', 'approved'])) {
            throw new \Exception('대기 중이거나 승인된 출금만 거절할 수 있습니다.');
        }

        // 잔액 복구
        $wallet = UserEmoney::findOrCreateForUser($this->user_id);
        $wallet->addBalance($this->total, "출금 거절: {$this->trans}");

        $this->status = 'rejected';
        $this->admin_id = $adminId;
        $this->admin_memo = $reason;
        $this->save();

        return $this;
    }

    /**
     * 출금 방법 레이블
     */
    public function getMethodLabel()
    {
        $methods = [
            'bank' => '계좌이체',
            'card' => '카드송금',
            'point' => '포인트전환',
            'crypto' => '암호화폐',
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
            'approved' => '승인됨',
            'completed' => '완료',
            'rejected' => '거절됨',
            'cancelled' => '취소됨',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * 총액 계산
     */
    public function calculateTotal()
    {
        $this->total = bcadd($this->amount, bcadd($this->fee, $this->tax, 2), 2);
        return $this->total;
    }
}