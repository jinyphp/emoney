# JwtAuth 파사드 사용법 (jiny/auth 패키지)

**중요**: jiny/emoney 패키지는 더 이상 자체 JwtAuth 파사드를 제공하지 않습니다.
대신 `jiny/auth` 패키지의 JwtAuth 파사드를 사용하세요.

## 패키지 변경 안내

### 이전 방식 (더 이상 사용하지 않음)
```php
use Jiny\Emoney\Facades\JwtAuth; // ❌ 제거됨
```

### 새로운 방식 (권장)
```php
use Jiny\Auth\Facades\JwtAuth; // ✅ 사용하세요
```

## 기본 사용법

### 네임스페이스 import

```php
use Jiny\Auth\Facades\JwtAuth;
```

### 현재 인증된 사용자 조회

```php
// 현재 요청에서 사용자 확인 (세션 + JWT 통합)
$user = JwtAuth::user($request);

// 현재 사용자 확인 (기본 세션)
$user = JwtAuth::user();

if ($user) {
    echo "사용자명: " . $user->name;
    echo "이메일: " . $user->email;
    echo "UUID: " . $user->uuid;
}
```

### 인증 상태 확인

```php
// 사용자가 인증되었는지 확인
if (JwtAuth::check($request)) {
    echo '로그인 상태입니다.';
} else {
    echo '로그인이 필요합니다.';
}
```

### 사용자 ID 조회

```php
// 현재 사용자의 UUID 또는 ID 반환
$userId = JwtAuth::id($request);
```

### UUID로 사용자 조회

```php
// 특정 UUID로 사용자 조회 (샤딩 지원)
$user = JwtAuth::getUserByUuid('6da333d2-eaf9-4362-95d2-0c0acf070f7b');

if ($user) {
    echo "찾은 사용자: " . $user->name;
}
```

### 여러 사용자 조회

```php
// 여러 UUID로 사용자 정보 일괄 조회
$uuids = [
    '6da333d2-eaf9-4362-95d2-0c0acf070f7b',
    '8e5a441d-9b2c-4e7f-a3d1-2c4b5a6e7f8g'
];

$users = JwtAuth::getUsersByUuids($uuids);

foreach ($users as $user) {
    echo "이름: " . $user['name'] . ", 이메일: " . $user['email'] . "\n";
}
```

### 샤딩 정보 조회

```php
// 사용자 UUID의 샤드 번호 조회
$shardNumber = JwtAuth::getShardNumber('6da333d2-eaf9-4362-95d2-0c0acf070f7b');
echo "샤드 번호: " . $shardNumber; // 예: 1

// 사용자 UUID의 샤드 테이블명 조회
$tableName = JwtAuth::getShardTableName('6da333d2-eaf9-4362-95d2-0c0acf070f7b');
echo "테이블명: " . $tableName; // 예: users_001
```

### JWT 토큰 관리

```php
// 토큰 쌍 생성 (Access + Refresh)
$tokens = JwtAuth::generateTokenPair($user);
/*
결과:
[
    'access_token' => 'eyJ0eXAiOiJKV1QiLCJhbGc...',
    'refresh_token' => 'eyJ0eXAiOiJKV1QiLCJhbGc...',
    'token_type' => 'Bearer',
    'expires_in' => 3600
]
*/

// Access Token 생성
$accessToken = JwtAuth::generateAccessToken($user);

// 토큰 검증
try {
    $jwtToken = JwtAuth::validateToken($tokenString);
    $userUuid = $jwtToken->claims()->get('sub');
} catch (\Exception $e) {
    echo "토큰이 유효하지 않습니다: " . $e->getMessage();
}

// 요청에서 토큰 추출
$token = JwtAuth::getTokenFromRequest($request);

// 토큰에서 사용자 정보 추출
$user = JwtAuth::getUserFromToken($tokenString);
```

## 실제 사용 예제

### jiny/emoney에서의 올바른 사용법

```php
<?php

namespace Jiny\Emoney\Http\Controllers\Emoney\Deposit;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jiny\Auth\Facades\JwtAuth; // ✅ jiny/auth 패키지 사용

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        // JWT 토큰 또는 세션으로 사용자 인증
        $user = JwtAuth::user($request);

        if (!$user) {
            return redirect()->route('login');
        }

        // 샤딩 정보 조회
        $shardNumber = JwtAuth::getShardNumber($user->uuid);
        $tableName = JwtAuth::getShardTableName($user->uuid);

        return view('jiny-emoney::emoney.deposit.index', [
            'user' => $user,
            'shard_info' => [
                'shard_number' => $shardNumber,
                'table_name' => $tableName
            ]
        ]);
    }
}
```

### API 컨트롤러에서의 사용

```php
<?php

namespace Jiny\Emoney\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jiny\Auth\Facades\JwtAuth;

class EmoneyApiController extends Controller
{
    public function getBalance(Request $request)
    {
        // JWT 토큰으로 사용자 인증
        $user = JwtAuth::user($request);

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 또는 더 간단하게
        if (!JwtAuth::check($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = JwtAuth::id($request);

        return response()->json([
            'user_id' => $userId,
            'balance' => 50000 // 실제 잔액 조회 로직
        ]);
    }

    public function login(Request $request)
    {
        // 로그인 로직...
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // JWT 토큰 생성
            $tokens = JwtAuth::generateTokenPair($user);

            return response()->json($tokens);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }
}
```

## 마이그레이션 가이드

### 1. import 구문 변경
```php
// 이전
use Jiny\Emoney\Facades\JwtAuth;

// 이후
use Jiny\Auth\Facades\JwtAuth;
```

### 2. 기능 차이 없음
기존에 사용하던 모든 메서드는 동일하게 작동합니다:
- `JwtAuth::user($request)`
- `JwtAuth::check($request)`
- `JwtAuth::getUserByUuid($uuid)`
- `JwtAuth::getShardNumber($uuid)`
- `JwtAuth::getShardTableName($uuid)`
- `JwtAuth::getUsersByUuids($uuids)`
- `JwtAuth::id($request)`

### 3. 추가된 기능
jiny/auth의 JwtAuth 파사드는 더 많은 JWT 토큰 관리 기능을 제공합니다:
- 토큰 생성: `generateTokenPair()`, `generateAccessToken()`, `generateRefreshToken()`
- 토큰 검증: `validateToken()`, `getTokenFromRequest()`
- 토큰 폐기: `revokeToken()`, `revokeAllUserTokens()`
- 사용자 추출: `getUserFromToken()`

## 주요 특징

### 1. 통합 인증 지원
- **세션 인증**: Laravel의 기본 Auth::user() 지원
- **JWT 인증**: Authorization 헤더, 쿠키, 쿼리 파라미터에서 토큰 자동 감지
- **샤딩 지원**: 기본 `users` 테이블과 샤딩된 `users_001`, `users_002` 등 테이블 모두 지원

### 2. 유연한 토큰 감지
JWT 토큰을 다음 위치에서 자동으로 감지합니다:
- `Authorization: Bearer {token}` 헤더
- `jwt_token` 쿠키
- `token` 쿼리 파라미터

### 3. 에러 처리
- 잘못된 토큰이나 만료된 토큰에 대해 안전하게 처리
- 디버그 로그 자동 기록

## 의존성 요구사항

jiny/emoney 패키지를 사용하려면 jiny/auth 패키지가 필요합니다:

```json
{
    "require": {
        "jiny/auth": "^0.5.0"
    }
}
```

## 관련 문서

- [jiny/auth 샤딩 시스템 가이드](../../../jiny/auth/docs/sharding.md)
- [JWT 인증 통합 가이드](../../../jiny/auth/docs/sharding.md#jwt-인증과-샤딩-통합)

## 테스트 예시

```php
// 테스트에서 사용 예시
public function test_user_authentication()
{
    $user = JwtAuth::getUserByUuid('test-uuid');
    $this->assertNotNull($user);
    $this->assertEquals('테스트사용자', $user->name);
}

public function test_jwt_token_generation()
{
    $user = User::factory()->create();
    $tokens = JwtAuth::generateTokenPair($user);

    $this->assertArrayHasKey('access_token', $tokens);
    $this->assertArrayHasKey('refresh_token', $tokens);
    $this->assertEquals('Bearer', $tokens['token_type']);
}
```

이제 jiny/emoney에서 jiny/auth의 통합된 JwtAuth 파사드를 사용하여 더 강력하고 일관된 JWT 인증 기능을 활용할 수 있습니다.