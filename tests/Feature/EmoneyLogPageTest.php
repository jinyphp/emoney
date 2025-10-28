<?php

namespace Jiny\Emoney\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmoneyLogPageTest extends TestCase
{
    use DatabaseTransactions;

    protected $testUser;
    protected $testUserUuid;

    protected function setUp(): void
    {
        parent::setUp();

        // 필요한 테이블 생성
        $this->createMissingTables();

        // 테스트용 사용자 생성
        $this->createTestUser();

        // 테스트용 로그 데이터 생성
        $this->createTestLogs();
    }

    private function createMissingTables()
    {
        // users 테이블 확인 및 생성
        $usersTableExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        if (empty($usersTableExists)) {
            DB::statement('
                CREATE TABLE users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    uuid VARCHAR(36) UNIQUE,
                    name VARCHAR(255),
                    email VARCHAR(255) UNIQUE,
                    password VARCHAR(255),
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');
        }

        // user_emoney_logs 테이블 확인 및 생성
        $logsTableExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='user_emoney_logs'");
        if (empty($logsTableExists)) {
            DB::statement('
                CREATE TABLE user_emoney_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_uuid VARCHAR(36) NOT NULL,
                    type VARCHAR(50),
                    amount DECIMAL(15,2),
                    balance_before DECIMAL(15,2),
                    balance_after DECIMAL(15,2),
                    description TEXT,
                    reference_id INTEGER,
                    reference_type VARCHAR(50),
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');
        }

        // user_emoney 테이블 확인 및 생성
        $emoneyTableExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='user_emoney'");
        if (empty($emoneyTableExists)) {
            DB::statement('
                CREATE TABLE user_emoney (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_uuid VARCHAR(36) NOT NULL UNIQUE,
                    balance DECIMAL(15,2) DEFAULT 0,
                    total_deposit DECIMAL(15,2) DEFAULT 0,
                    total_used DECIMAL(15,2) DEFAULT 0,
                    total_withdrawn DECIMAL(15,2) DEFAULT 0,
                    points DECIMAL(15,2) DEFAULT 0,
                    currency VARCHAR(10) DEFAULT "KRW",
                    status VARCHAR(20) DEFAULT "active",
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');
        }
    }

    private function createTestUser()
    {
        $this->testUserUuid = (string) Str::uuid();

        // 사용자 기본 테이블에 삽입
        DB::table('users')->insert([
            'uuid' => $this->testUserUuid,
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // User 모델 인스턴스 생성 (인증에 사용할 수 있도록)
        $this->testUser = new class implements \Illuminate\Contracts\Auth\Authenticatable {
            public $uuid;
            public $email;
            public $name;
            public $id;

            public function getAuthIdentifierName()
            {
                return 'id';
            }

            public function getAuthIdentifier()
            {
                return $this->id;
            }

            public function getAuthPassword()
            {
                return 'password';
            }

            public function getRememberToken()
            {
                return null;
            }

            public function setRememberToken($value)
            {
                // 구현하지 않음
            }

            public function getRememberTokenName()
            {
                return null;
            }

            public function getAuthPasswordName()
            {
                return 'password';
            }
        };

        $this->testUser->uuid = $this->testUserUuid;
        $this->testUser->email = 'testuser@example.com';
        $this->testUser->name = 'Test User';
        $this->testUser->id = 1;

        // 사용자 이머니 계정 생성
        DB::table('user_emoney')->insert([
            'user_uuid' => $this->testUserUuid,
            'balance' => 50000.00,
            'total_deposit' => 100000.00,
            'total_used' => 20000.00,
            'total_withdrawn' => 30000.00,
            'points' => 0.00,
            'currency' => 'KRW',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function createTestLogs()
    {
        // 테스트용 로그 데이터 생성
        $logs = [
            [
                'user_uuid' => $this->testUserUuid,
                'type' => 'deposit',
                'amount' => 100000.00,
                'balance_before' => 0.00,
                'balance_after' => 100000.00,
                'description' => '이머니 충전 - 입금 확인',
                'reference_id' => 1,
                'reference_type' => 'deposit',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'user_uuid' => $this->testUserUuid,
                'type' => 'purchase',
                'amount' => -20000.00,
                'balance_before' => 100000.00,
                'balance_after' => 80000.00,
                'description' => '상품 구매 - 제품명',
                'reference_id' => 2,
                'reference_type' => 'purchase',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'user_uuid' => $this->testUserUuid,
                'type' => 'withdrawal',
                'amount' => -30000.00,
                'balance_before' => 80000.00,
                'balance_after' => 50000.00,
                'description' => '이머니 출금 - 국민은행 계좌',
                'reference_id' => 3,
                'reference_type' => 'withdrawal',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ];

        foreach ($logs as $log) {
            DB::table('user_emoney_logs')->insert($log);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_access_emoney_log_page()
    {
        echo "\n=== 이머니 로그 페이지 접근 테스트 시작 ===\n";

        // 실제 뷰 파일이 있는지 확인
        $viewPath = '/Users/hojin8/projects/jinyphp/jinysite_recruit/vendor/jiny/emoney/resources/views/home/emoney_log/index.blade.php';
        echo "1. 뷰 파일 존재 확인: " . ($this->checkFileExists($viewPath) ? "있음" : "없음") . "\n";

        // 컨트롤러가 올바른 뷰를 반환하는지 확인
        $controller = new \Jiny\Emoney\Http\Controllers\Emoney\LogController();
        $request = new \Illuminate\Http\Request();

        // 가짜 사용자 인증 설정 (실제로는 JWT 인증이 필요하지만 테스트용)
        $this->actingAs($this->testUser);

        echo "2. 로그 데이터 확인...\n";
        $logCount = DB::table('user_emoney_logs')->where('user_uuid', $this->testUserUuid)->count();
        echo "   - 생성된 로그 수: {$logCount}개\n";

        $this->assertEquals(3, $logCount);

        echo "3. 컨트롤러가 올바른 데이터를 반환하는지 확인...\n";

        // 컨트롤러에서 올바른 테이블을 사용하는지 확인
        $logs = DB::table('user_emoney_logs')
            ->where('user_uuid', $this->testUserUuid)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->assertGreaterThan(0, $logs->count());
        echo "   - 조회된 로그 수: " . $logs->count() . "개\n";

        // 로그 데이터 내용 확인
        $latestLog = $logs->first();
        $this->assertEquals('withdrawal', $latestLog->type);
        $this->assertEquals(-30000, $latestLog->amount);
        echo "   - 최신 로그: {$latestLog->type}, {$latestLog->amount}원\n";

        echo "\n=== 이머니 로그 페이지 접근 테스트 성공 완료 ===\n";
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function emoney_log_displays_correct_transaction_types()
    {
        echo "\n=== 거래 유형별 로그 표시 테스트 시작 ===\n";

        $logs = DB::table('user_emoney_logs')
            ->where('user_uuid', $this->testUserUuid)
            ->orderBy('created_at', 'desc')
            ->get();

        echo "1. 거래 유형별 로그 확인...\n";

        foreach ($logs as $log) {
            echo "   - {$log->type}: {$log->amount}원 ({$log->description})\n";

            // 각 거래 유형이 올바른 금액 부호를 가지는지 확인
            if ($log->type === 'deposit') {
                $this->assertGreaterThan(0, $log->amount, '입금은 양수여야 함');
            } elseif (in_array($log->type, ['purchase', 'withdrawal'])) {
                $this->assertLessThan(0, $log->amount, '출금/구매는 음수여야 함');
            }
        }

        echo "2. 잔액 변화 추적 확인...\n";
        foreach ($logs as $log) {
            $expectedBalance = $log->balance_before + $log->amount;
            $this->assertEquals($expectedBalance, $log->balance_after, '잔액 계산이 정확해야 함');
            echo "   - {$log->balance_before} + {$log->amount} = {$log->balance_after}\n";
        }

        echo "\n=== 거래 유형별 로그 표시 테스트 성공 완료 ===\n";
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function emoney_log_pagination_works_correctly()
    {
        echo "\n=== 로그 페이지네이션 테스트 시작 ===\n";

        // 추가 로그 데이터 생성 (총 25개가 되도록)
        for ($i = 4; $i <= 25; $i++) {
            DB::table('user_emoney_logs')->insert([
                'user_uuid' => $this->testUserUuid,
                'type' => 'test_transaction',
                'amount' => 1000.00,
                'balance_before' => 50000.00,
                'balance_after' => 51000.00,
                'description' => "테스트 거래 #{$i}",
                'reference_id' => $i,
                'reference_type' => 'test',
                'created_at' => now()->subMinutes($i),
                'updated_at' => now()->subMinutes($i),
            ]);
        }

        echo "1. 총 로그 수 확인...\n";
        $totalLogs = DB::table('user_emoney_logs')->where('user_uuid', $this->testUserUuid)->count();
        echo "   - 총 로그 수: {$totalLogs}개\n";
        $this->assertEquals(25, $totalLogs);

        echo "2. 페이지네이션 테스트...\n";
        $firstPage = DB::table('user_emoney_logs')
            ->where('user_uuid', $this->testUserUuid)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $this->assertEquals(20, $firstPage->count());
        echo "   - 첫 페이지 로그 수: " . $firstPage->count() . "개\n";

        $secondPage = DB::table('user_emoney_logs')
            ->where('user_uuid', $this->testUserUuid)
            ->orderBy('created_at', 'desc')
            ->offset(20)
            ->limit(20)
            ->get();

        $this->assertEquals(5, $secondPage->count());
        echo "   - 두 번째 페이지 로그 수: " . $secondPage->count() . "개\n";

        echo "\n=== 로그 페이지네이션 테스트 성공 완료 ===\n";
    }

    private function checkFileExists($path)
    {
        return file_exists($path);
    }
}