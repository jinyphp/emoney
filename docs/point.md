# Jiny Point 시스템 매뉴얼

## 개요

Jiny Point 시스템은 사용자 참여를 증진하고 고객 충성도를 높이기 위한 포인트 적립 및 사용 관리 시스템입니다. 다양한 활동을 통해 포인트를 적립하고, 할인이나 상품 교환에 사용할 수 있는 완전한 리워드 플랫폼을 제공합니다.

## 시스템 특징

### 주요 기능
- **다양한 적립 방식**: 구매, 리뷰, 이벤트, 출석체크 등
- **유연한 사용 정책**: 할인, 상품 교환, 쿠폰 교환 등
- **자동 만료 관리**: 선입선출 방식의 스마트한 만료 처리
- **실시간 알림**: 적립, 사용, 만료 예정 알림
- **상세한 추적**: 모든 포인트 거래의 완전한 감사 추적
- **샤딩 지원**: 대용량 사용자를 위한 확장 가능한 구조

### 포인트 정책 특징
- **FIFO 만료**: 먼저 적립된 포인트부터 자동 사용
- **부분 사용**: 필요한 만큼만 사용 가능
- **환불 지원**: 주문 취소 시 포인트 환불
- **관리자 조정**: 이벤트 지급, 보상, 차감 등

## 데이터베이스 구조

### 핵심 테이블

#### user_point (사용자 포인트 계정)
```sql
- id: 포인트 계정 고유 ID
- user_uuid: 사용자 UUID (샤딩 지원)
- shard_id: 샤드 번호 (0-15)
- email: 사용자 이메일 (캐시)
- name: 사용자 이름 (캐시)
- balance: 현재 포인트 잔액
- available_balance: 사용 가능한 포인트 (만료일 고려)
- total_earned: 총 적립 포인트
- total_used: 총 사용 포인트
- total_expired: 총 만료 포인트
- total_refunded: 총 환불 포인트
- expiring_soon: 곧 만료될 포인트 (7일 이내)
- last_activity_at: 마지막 활동 시간
- status: 계정 상태 (active/inactive/suspended)
```

#### user_point_log (포인트 거래 로그)
```sql
- id: 로그 고유 ID
- user_id: 사용자 ID (기존 시스템 호환)
- user_uuid: 사용자 UUID
- shard_id: 샤드 번호
- transaction_type: 거래 유형 (earn/use/refund/expire/admin)
- amount: 거래 포인트 (양수: 적립, 음수: 사용)
- balance_before: 거래 전 잔액
- balance_after: 거래 후 잔액
- reason: 거래 사유
- reference_type: 참조 유형 (order/event/review/admin)
- reference_id: 참조 ID
- expires_at: 만료일 (적립 시에만)
- admin_id: 관리자 ID (관리자 거래 시)
- meta_data: 추가 정보 (JSON)
- created_at: 생성 시간
```

#### user_point_expiry (포인트 만료 스케줄)
```sql
- id: 만료 스케줄 ID
- user_id: 사용자 ID
- user_uuid: 사용자 UUID
- shard_id: 샤드 번호
- point_log_id: 연결된 포인트 로그 ID
- amount: 만료 대상 포인트
- original_amount: 원래 적립 포인트
- used_amount: 이미 사용된 포인트
- expires_at: 만료일
- expired: 만료 처리 여부
- expired_at: 실제 만료 처리 시간
- notification_sent: 알림 발송 여부
- created_at: 생성 시간
```

## UserPoint 모델 API

### 기본 사용법

#### 사용자 포인트 계정 생성/조회
```php
// 사용자 ID로 포인트 계정 조회 또는 생성
$userPoint = UserPoint::findOrCreateForUser($userId);

// UUID로 직접 조회
$userPoint = UserPoint::where('user_uuid', $userUuid)->first();
```

#### 포인트 잔액 조회
```php
// 현재 포인트 잔액
$balance = $userPoint->balance;

// 사용 가능한 포인트 (만료일 고려)
$availableBalance = $userPoint->available_balance;

// 총 적립 포인트
$totalEarned = $userPoint->total_earned;

// 총 사용 포인트
$totalUsed = $userPoint->total_used;

// 곧 만료될 포인트 (7일 이내)
$expiringSoon = $userPoint->expiring_soon;
```

### 핵심 거래 메소드

#### 1. 포인트 적립 (earnPoints)
```php
/**
 * 포인트 적립 및 로그 기록
 * @param int $amount 적립할 포인트
 * @param string $reason 적립 사유
 * @param string $referenceType 참조 유형 (order/event/review/admin)
 * @param int $referenceId 참조 ID
 * @param DateTime $expiresAt 만료일 (선택)
 * @param int $adminId 관리자 ID (관리자 적립 시)
 * @return UserPointLog
 */

// 구매 적립 (1년 후 만료)
$log = $userPoint->earnPoints(
    1000,
    '상품 구매 적립',
    'order',
    $orderId,
    now()->addYear()
);

// 이벤트 적립 (30일 후 만료)
$log = $userPoint->earnPoints(
    500,
    '신규가입 이벤트',
    'event',
    $eventId,
    now()->addDays(30)
);

// 리뷰 적립 (만료일 없음)
$log = $userPoint->earnPoints(
    100,
    '상품 리뷰 작성',
    'review',
    $reviewId
);
```

#### 2. 포인트 사용 (usePoints)
```php
/**
 * 포인트 사용 및 로그 기록
 * @param int $amount 사용할 포인트
 * @param string $reason 사용 사유
 * @param string $referenceType 참조 유형
 * @param int $referenceId 참조 ID
 * @param int $adminId 관리자 ID (선택)
 * @throws Exception 잔액 부족 시
 * @return UserPointLog
 */

try {
    // 할인 사용
    $log = $userPoint->usePoints(
        800,
        '상품 할인 사용',
        'order',
        $orderId
    );

    // 쿠폰 교환
    $log = $userPoint->usePoints(
        1500,
        '10% 할인쿠폰 교환',
        'coupon',
        $couponId
    );

} catch (Exception $e) {
    echo "포인트 부족: " . $e->getMessage();
}
```

#### 3. 포인트 환불 (refundPoints)
```php
/**
 * 포인트 환불 처리
 * @param int $amount 환불할 포인트
 * @param string $reason 환불 사유
 * @param string $referenceType 참조 유형
 * @param int $referenceId 참조 ID
 * @param int $adminId 관리자 ID (선택)
 * @return UserPointLog
 */

// 주문 취소 시 포인트 환불
$log = $userPoint->refundPoints(
    800,
    '주문 취소로 인한 환불',
    'order',
    $orderId
);
```

#### 4. 포인트 만료 (expirePoints)
```php
/**
 * 포인트 만료 처리
 * @param int $amount 만료할 포인트
 * @param string $reason 만료 사유
 * @param int $adminId 관리자 ID (선택)
 * @return UserPointLog
 */

// 자동 만료 처리 (배치 작업에서 호출)
$log = $userPoint->expirePoints(
    500,
    '유효기간 만료'
);
```

#### 5. 관리자 조정 (adminAdjustment)
```php
/**
 * 관리자 포인트 지급/차감
 * @param int $amount 조정할 포인트 (양수: 지급, 음수: 차감)
 * @param string $reason 조정 사유
 * @param int $adminId 관리자 ID
 * @return UserPointLog
 */

// 이벤트 포인트 지급
$log = $userPoint->adminAdjustment(
    5000,
    '1주년 기념 이벤트 지급',
    $adminId
);

// 부정 사용 차감
$log = $userPoint->adminAdjustment(
    -2000,
    '부정 사용으로 인한 차감',
    $adminId
);
```

#### 6. 만료 예정 포인트 조회 (getExpiringPoints)
```php
/**
 * 만료 예정 포인트 조회
 * @param int $days 며칠 이내 (기본: 30일)
 * @return Collection
 */

// 30일 이내 만료 예정 포인트
$expiringPoints = $userPoint->getExpiringPoints(30);

// 7일 이내 만료 예정 포인트
$urgentExpiring = $userPoint->getExpiringPoints(7);

foreach ($expiringPoints as $expiry) {
    echo "만료일: " . $expiry->expires_at;
    echo "포인트: " . $expiry->amount;
    echo "적립 사유: " . $expiry->pointLog->reason;
}
```

## 사용자 인터페이스

### 웹 라우트 구조

#### 포인트 메인 대시보드
```
GET /home/emoney/point
- 현재 포인트 잔액 표시
- 사용 가능한 포인트 (만료일 고려)
- 곧 만료될 포인트 알림
- 최근 포인트 활동
- 월별 적립/사용 통계
```

#### 포인트 거래 내역
```
GET /home/emoney/point/log
- 모든 포인트 거래 내역
- 거래 유형별 필터링
- 기간별 조회
- 상세 거래 정보
```

#### 만료 예정 포인트
```
GET /home/emoney/point/expiry
- 만료 예정 포인트 목록
- 만료일별 정렬
- 적립 경로별 분류
- 만료 알림 설정
```

### 사용자 가이드

#### 1. 포인트 적립하기

**구매 적립**
- 상품 구매 시 자동 적립
- 적립률: 구매 금액의 1-5% (등급별 차등)
- 적립 시점: 주문 확정 후
- 유효기간: 적립일로부터 1년

**활동 적립**
```php
// 리뷰 작성 적립
$userPoint->earnPoints(100, '상품 리뷰 작성', 'review', $reviewId);

// 출석체크 적립
$userPoint->earnPoints(10, '일일 출석체크', 'daily', date('Y-m-d'));

// 추천인 적립
$userPoint->earnPoints(1000, '친구 추천 보너스', 'referral', $referredUserId);
```

**이벤트 적립**
- 신규가입: 1,000P
- 첫 구매: 2,000P
- 생일 축하: 500P
- 등급 승급: 3,000P

#### 2. 포인트 사용하기

**할인 사용**
```html
<!-- 주문 페이지에서 포인트 사용 -->
<form method="POST" action="/checkout">
    <div class="point-usage">
        <label>포인트 사용</label>
        <input type="number" name="point_amount" max="{{$userPoint->balance}}" min="0">
        <span>사용 가능: {{number_format($userPoint->balance)}}P</span>
    </div>
    <button type="submit">주문하기</button>
</form>
```

**상품 교환**
- 기프트카드: 10,000P
- 할인쿠폰: 1,000P ~ 5,000P
- 무료배송권: 500P
- 특별 상품: 포인트 전용 상품

**사용 제한**
- 최소 사용: 100P 단위
- 최대 사용: 주문 금액의 50%
- 중복 할인: 쿠폰과 동시 사용 불가

#### 3. 만료 관리

**만료 알림**
- 7일 전: 이메일 + SMS 알림
- 1일 전: 푸시 알림
- 만료 당일: 최종 알림

**만료 방지**
- 포인트 선물하기
- 기부하기
- 쿠폰으로 교환

## 관리자 기능

### 관리자 라우트

#### 포인트 관리
```
GET /admin/auth/emoney/point           # 사용자 포인트 목록
GET /admin/auth/emoney/point/{id}      # 사용자 포인트 상세
POST /admin/auth/emoney/point/{id}/adjust # 포인트 조정
GET /admin/auth/emoney/point/logs      # 포인트 거래 로그
GET /admin/auth/emoney/point/expiry    # 만료 예정 포인트
```

#### 포인트 정책 설정
```
GET /admin/auth/emoney/point/settings  # 포인트 정책 설정
POST /admin/auth/emoney/point/settings # 정책 저장
GET /admin/auth/emoney/point/stats     # 포인트 통계
```

### 관리자 운영 가이드

#### 1. 포인트 정책 관리

**적립 정책 설정**
```json
{
    "earn_rates": {
        "purchase": {
            "basic": 0.01,      // 일반회원 1%
            "silver": 0.02,     // 실버회원 2%
            "gold": 0.03,       // 골드회원 3%
            "platinum": 0.05    // 플래티넘회원 5%
        },
        "review": 100,          // 리뷰 작성 시 100P
        "daily_check": 10,      // 출석체크 시 10P
        "referral": 1000        // 추천 시 1000P
    },
    "expiry_policy": {
        "default_days": 365,    // 기본 유효기간 365일
        "event_days": 30,       // 이벤트 포인트 30일
        "bonus_days": 90        // 보너스 포인트 90일
    },
    "usage_policy": {
        "min_amount": 100,      // 최소 사용 단위
        "max_rate": 0.5,        // 최대 사용 비율 50%
        "blacklist": ["delivery", "tax"]  // 사용 불가 항목
    }
}
```

#### 2. 대량 포인트 지급

**이벤트 포인트 지급**
```php
// 전체 회원 대상
$users = User::where('status', 'active')->get();

foreach ($users as $user) {
    $userPoint = UserPoint::findOrCreateForUser($user->id);
    $userPoint->earnPoints(
        1000,
        '1주년 기념 이벤트 지급',
        'event',
        $eventId,
        now()->addDays(30),
        $adminId
    );
}

// 특정 등급 대상
$vipUsers = User::where('grade', 'platinum')->get();
foreach ($vipUsers as $user) {
    $userPoint = UserPoint::findOrCreateForUser($user->id);
    $userPoint->earnPoints(5000, 'VIP 특별 지급', 'event', $eventId);
}
```

#### 3. 만료 처리 관리

**배치 만료 처리**
```php
// 일일 만료 처리 배치 작업
class ExpirePointsCommand extends Command
{
    public function handle()
    {
        $today = now()->startOfDay();

        // 만료 대상 포인트 조회
        $expiringPoints = UserPointExpiry::where('expires_at', '<', $today)
            ->where('expired', false)
            ->get();

        foreach ($expiringPoints as $expiry) {
            $userPoint = UserPoint::where('user_uuid', $expiry->user_uuid)->first();

            if ($userPoint) {
                // 포인트 만료 처리
                $userPoint->expirePoints(
                    $expiry->amount,
                    '유효기간 만료'
                );

                // 만료 스케줄 업데이트
                $expiry->update([
                    'expired' => true,
                    'expired_at' => now()
                ]);

                // 만료 알림 발송
                $this->sendExpiryNotification($userPoint, $expiry->amount);
            }
        }
    }
}
```

## API 문서

### REST API 엔드포인트

#### 포인트 잔액 조회
```http
GET /api/point/balance
Authorization: Bearer {jwt_token}

Response:
{
    "success": true,
    "data": {
        "balance": 15000,
        "available_balance": 14500,
        "total_earned": 50000,
        "total_used": 32000,
        "total_expired": 3000,
        "expiring_soon": 500,
        "last_activity_at": "2024-10-28T10:30:00Z"
    }
}
```

#### 포인트 사용
```http
POST /api/point/use
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "amount": 1000,
    "reference_type": "order",
    "reference_id": 123,
    "reason": "할인 사용"
}

Response:
{
    "success": true,
    "data": {
        "transaction_id": 456,
        "amount": -1000,
        "balance_before": 15000,
        "balance_after": 14000,
        "reason": "할인 사용"
    }
}
```

#### 포인트 내역 조회
```http
GET /api/point/transactions?page=1&limit=20&type=all&from=2024-10-01&to=2024-10-31
Authorization: Bearer {jwt_token}

Response:
{
    "success": true,
    "data": {
        "transactions": [
            {
                "id": 789,
                "transaction_type": "earn",
                "amount": 1000,
                "balance_before": 14000,
                "balance_after": 15000,
                "reason": "상품 구매 적립",
                "reference_type": "order",
                "reference_id": 123,
                "expires_at": "2025-10-28T00:00:00Z",
                "created_at": "2024-10-28T10:30:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 3,
            "total_count": 58
        }
    }
}
```

#### 만료 예정 포인트 조회
```http
GET /api/point/expiry?days=30
Authorization: Bearer {jwt_token}

Response:
{
    "success": true,
    "data": {
        "expiring_points": [
            {
                "amount": 500,
                "expires_at": "2024-11-05T00:00:00Z",
                "reason": "이벤트 참여 적립",
                "days_remaining": 8
            }
        ],
        "total_expiring": 1500
    }
}
```

## 배치 작업 및 스케줄링

### 필수 배치 작업

#### 1. 포인트 만료 처리
```bash
# 매일 자정에 실행
0 0 * * * php artisan point:expire-batch

# 수동 실행
php artisan point:expire-batch --date=2024-10-28
```

#### 2. 만료 예정 알림
```bash
# 매일 오전 9시에 실행
0 9 * * * php artisan point:expiry-notification

# 7일 전 알림
php artisan point:expiry-notification --days=7

# 1일 전 알림
php artisan point:expiry-notification --days=1
```

#### 3. 포인트 정합성 검사
```bash
# 매주 일요일 새벽 2시
0 2 * * 0 php artisan point:balance-verify

# 특정 사용자 검증
php artisan point:balance-verify --user-uuid={uuid}

# 문제 발견 시 자동 수정
php artisan point:balance-verify --fix
```

#### 4. 통계 생성
```bash
# 매일 새벽 1시
0 1 * * * php artisan point:daily-stats

# 월말 통계
php artisan point:monthly-stats --month=2024-10
```

## 성능 최적화

### 데이터베이스 최적화

#### 인덱스 설정
```sql
-- 사용자별 포인트 조회 최적화
CREATE INDEX idx_point_user_status ON user_point(user_uuid, status);

-- 거래 내역 조회 최적화
CREATE INDEX idx_point_log_user_date ON user_point_log(user_uuid, created_at);

-- 만료 처리 최적화
CREATE INDEX idx_point_expiry_date ON user_point_expiry(expires_at, expired);

-- 거래 유형별 조회 최적화
CREATE INDEX idx_point_log_type_date ON user_point_log(transaction_type, created_at);
```

### 캐싱 전략

#### Redis 캐싱
```php
// 사용자 포인트 잔액 캐싱 (10분)
Cache::remember("point:balance:{$userUuid}", 600, function () use ($userUuid) {
    return UserPoint::where('user_uuid', $userUuid)->first();
});

// 만료 예정 포인트 캐싱 (1시간)
Cache::remember("point:expiring:{$userUuid}", 3600, function () use ($userUuid) {
    $userPoint = UserPoint::where('user_uuid', $userUuid)->first();
    return $userPoint ? $userPoint->getExpiringPoints(7) : collect();
});
```

## 트러블슈팅

### 자주 발생하는 문제

#### 1. 포인트 잔액 불일치
**증상**: 거래 로그 합계와 현재 잔액이 다름
```php
// 진단 명령어
php artisan point:balance-verify --user-uuid={uuid} --verbose

// 자동 수정
php artisan point:balance-verify --user-uuid={uuid} --fix
```

#### 2. 만료 처리 지연
**증상**: 만료일이 지났지만 포인트가 만료되지 않음
```php
// 수동 만료 처리
php artisan point:expire-batch --force --date=2024-10-28

// 특정 사용자 만료 처리
php artisan point:expire-user --user-uuid={uuid}
```

## 지원 및 문의

### 기술 지원
- **이메일**: point-support@jiny.dev
- **문서**: https://docs.jiny.dev/point
- **GitHub**: https://github.com/jinyphp/emoney/issues
- **커뮤니티**: https://community.jiny.dev

### 운영 지원
- **운영팀**: operation@jiny.dev
- **긴급상황**: emergency@jiny.dev
- **정책 문의**: policy@jiny.dev

---

본 문서는 Jiny Point 시스템의 완전한 가이드를 제공합니다. 포인트 시스템 운영 중 궁금한 점이나 기술적 지원이 필요한 경우 위의 연락처로 문의해 주시기 바랍니다.
