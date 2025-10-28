<?php

namespace Jiny\Emoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

/**
 * UserEmoneyBank 모델 - 사용자 은행계좌 정보
 *
 * [모델 역할 및 목적]
 * - 사용자가 등록한 개인 은행계좌 정보 관리
 * - 이머니 입출금을 위한 연결 계좌 저장
 * - 사용자별 다중 계좌 지원 (기본 계좌 설정 가능)
 * - 국내외 은행 계좌 정보 및 SWIFT 코드 관리
 *
 * [테이블 구조]
 * - 테이블명: user_emoney_bank
 * - 기본키: id (auto increment)
 * - 외래키: user_id (사용자 ID 참조)
 * - 타임스탬프: created_at, updated_at
 *
 * [주요 컬럼]
 * - user_id: 사용자 ID (외래키)
 * - email: 사용자 이메일 (중복 저장)
 * - type: 계좌 유형 (savings, checking, business 등)
 * - currency: 통화 (KRW, USD, EUR 등)
 * - swift: SWIFT 코드 (국제송금용)
 * - bank: 은행명
 * - account: 계좌번호
 * - owner: 계좌 소유자명
 * - description: 계좌 설명/메모
 * - enable: 활성화 상태 (boolean)
 * - default: 기본 계좌 여부 (boolean)
 * - status: 계좌 상태 (active, inactive, blocked)
 *
 * [엘로퀀트 관계 (Relationships)]
 * - user(): 사용자 정보 (belongsTo)
 * - deposits(): 이 계좌로부터의 충전 기록 (hasMany)
 * - withdrawals(): 이 계좌로의 출금 기록 (hasMany)
 *
 * [핵심 메소드]
 * - setAsDefault(): 해당 계좌를 기본 계좌로 설정
 * - isActive(): 활성 상태 확인 (enable + status 체크)
 * - isDefault(): 기본 계좌 여부 확인
 * - getDefaultForUser($userId): 사용자의 기본 계좌 조회
 * - getActiveForUser($userId): 사용자의 활성 계좌 목록 조회
 *
 * [기본 계좌 관리]
 * - 사용자당 하나의 기본 계좌만 설정 가능
 * - 새로운 기본 계좌 설정 시 기존 기본 계좌는 자동 해제
 * - 출금/입금 시 기본 계좌가 우선 선택됨
 * - 기본 계좌는 반드시 활성 상태여야 함
 *
 * [상태 관리]
 * - enable: 계좌 사용 가능 여부 (사용자 설정)
 * - status: 계좌 상태 (시스템 관리)
 *   - active: 정상 사용 가능
 *   - inactive: 비활성 (임시 사용 중지)
 *   - blocked: 차단됨 (보안 이슈 등)
 *
 * [보안 고려사항]
 * - 계좌번호는 마스킹 처리하여 표시 권장
 * - 본인 명의 계좌만 등록 가능 (실명 확인)
 * - SWIFT 코드 유효성 검증 필요
 * - 계좌 정보 변경 시 로그 기록 권장
 *
 * [사용 예시]
 * ```php
 * // 사용자의 활성 계좌 목록 조회
 * $accounts = UserEmoneyBank::getActiveForUser($userId);
 *
 * // 기본 계좌 조회
 * $defaultAccount = UserEmoneyBank::getDefaultForUser($userId);
 *
 * // 새 계좌를 기본 계좌로 설정
 * $account = UserEmoneyBank::find($accountId);
 * $account->setAsDefault();
 *
 * // 계좌 상태 확인
 * if ($account->isActive()) {
 *     // 출금/입금 처리 가능
 * }
 * ```
 *
 * [관련 모델]
 * - User: 사용자 정보
 * - AuthBank: 인증된 은행 마스터 데이터
 * - UserEmoneyDeposit: 충전 기록 (계좌 참조)
 * - UserEmoneyWithdraw: 출금 기록 (계좌 참조)
 * - UserEmoneyLog: 거래 로그 (계좌 정보 포함)
 *
 * [데이터 무결성]
 * - 사용자당 기본 계좌는 하나만 존재
 * - 삭제된 계좌는 기록 보존을 위해 비활성화 처리 권장
 * - 외래키 제약조건으로 사용자 존재성 보장
 * - enable과 status 두 필드로 이중 상태 관리
 *
 * [국제화 지원]
 * - currency 필드로 다중 통화 지원
 * - SWIFT 코드로 국제 송금 지원
 * - 국가별 계좌번호 형식 검증 고려
 */
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