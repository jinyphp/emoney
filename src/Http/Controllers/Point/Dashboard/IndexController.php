<?php

namespace Jiny\Emoney\Http\Controllers\Point\Dashboard;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 포인트 대시보드
 *
 * [메소드 호출 관계 트리]
 * IndexController
 * └── __invoke(Request $request)
 *     ├── getAuthenticatedUser($request) - JWT 다중 인증 방식으로 사용자 확인
 *     ├── 사용자 포인트 계정 조회/생성
 *     │   ├── DB::table('user_point')->where('user_id', $userId)->first() - 기본 조회
 *     │   ├── DB::table('user_point')->where('user_uuid', $userUuid)->first() - UUID 보조 조회
 *     │   └── DB::table('user_point')->insert() - 계정 없으면 자동 생성
 *     ├── 최근 포인트 거래 내역 조회
 *     │   └── DB::table('user_point_log') - 포인트 거래 로그
 *     │       ├── ->where('user_id', $userId) - 해당 사용자만
 *     │       ├── ->orderBy('created_at', 'desc') - 최신순 정렬
 *     │       └── ->limit(10) - 최대 10개
 *     ├── 만료 예정 포인트 조회
 *     │   └── DB::table('user_point_expiry') - 포인트 만료 스케줄
 *     │       ├── ->where('user_id', $userId) - 해당 사용자만
 *     │       ├── ->where('expired', false) - 아직 만료되지 않은 것만
 *     │       ├── ->where('expires_at', '<=', now()->addDays(30)) - 30일 이내
 *     │       └── ->orderBy('expires_at', 'asc') - 만료일 오름차순
 *     ├── 통계 정보 계산
 *     │   ├── balance - 현재 포인트 잔액
 *     │   ├── total_earned - 총 적립 포인트
 *     │   ├── total_used - 총 사용 포인트
 *     │   ├── total_expired - 총 만료 포인트
 *     │   ├── expiring_soon - 30일 이내 만료 예정 포인트
 *     │   ├── recent_earn - 최근 30일 적립 포인트
 *     │   └── recent_use - 최근 30일 사용 포인트
 *     └── view('jiny-emoney::home.point.index', $data) - 포인트 대시보드 뷰 렌더링
 *
 * [컨트롤러 역할]
 * - 사용자의 포인트 대시보드 제공
 * - 포인트 잔액 및 사용 현황 표시
 * - 최근 거래 내역 요약 제공
 * - 만료 예정 포인트 알림
 * - 포인트 관련 통계 정보 제공
 * - 포인트 계정 자동 생성 (신규 사용자)
 *
 * [대시보드 구성 요소]
 * 1. 포인트 잔액 섹션
 *    - 현재 보유 포인트
 *    - 사용 가능 포인트
 *    - 만료 예정 포인트 알림
 *
 * 2. 최근 활동 섹션
 *    - 최근 10개 거래 내역
 *    - 적립/사용 구분 표시
 *    - 거래 일시 및 사유
 *
 * 3. 통계 정보 섹션
 *    - 총 적립/사용/만료 포인트
 *    - 최근 30일 활동 요약
 *    - 포인트 활용도 지표
 *
 * 4. 만료 예정 섹션
 *    - 30일 이내 만료 예정 포인트
 *    - 만료일별 그룹핑
 *    - 빠른 사용 유도 링크
 *
 * [계정 자동 생성]
 * - 포인트 계정이 없는 신규 사용자 대응
 * - user_id와 user_uuid 모두 저장
 * - 기본값 0으로 초기화
 * - 생성 후 즉시 조회하여 뷰 데이터 제공
 *
 * [듀얼 조회 시스템]
 * - user_id 우선 조회 (기존 시스템 호환)
 * - user_uuid 보조 조회 (샤딩 시스템 대응)
 * - 점진적 마이그레이션 지원
 * - 데이터 일관성 유지
 *
 * [통계 계산 로직]
 * - balance: user_point 테이블의 현재 잔액
 * - total_*: user_point 테이블의 누적 통계
 * - expiring_soon: 만료 예정 포인트 금액 합계
 * - recent_*: 최근 30일 거래 로그 집계
 *
 * [성능 최적화]
 * - 제한된 데이터 조회 (최근 10개, 30일 이내)
 * - 인덱스 활용: user_id, created_at, expires_at
 * - 조건부 쿼리: 사용자 정보 존재 시에만 실행
 * - 예외 무시: 테이블 미존재 시 성능 저하 방지
 *
 * [보안 고려사항]
 * - JWT 토큰 기반 사용자 인증
 * - 본인의 포인트 정보만 조회 가능
 * - SQL 인젝션 방지 (Query Builder 사용)
 * - 예외 처리로 시스템 정보 노출 방지
 *
 * [예외 처리 정책]
 * - 테이블 미존재: 서비스 초기 단계 대응
 * - 연결 실패: 일시적 장애 상황 대응
 * - 계정 미존재: 자동 생성으로 해결
 * - 로그 기록: 문제 추적 및 모니터링
 *
 * [라우트 연결]
 * Route: GET /point
 * Name: home.emoney.point.index
 *
 * [관련 컨트롤러]
 * - Point\Log\IndexController: 포인트 거래 로그
 * - Point\Expiry\IndexController: 포인트 만료 관리
 *
 * [뷰 데이터]
 * - user: 사용자 정보
 * - userPoint: 포인트 계정 정보
 * - recentLogs: 최근 포인트 거래 내역 (10개)
 * - expiringPoints: 만료 예정 포인트 목록
 * - statistics: 포인트 사용 통계 정보
 *
 * [사용자 경험]
 * - 직관적인 포인트 현황 표시
 * - 시각적 통계 및 차트
 * - 만료 예정 알림 강조
 * - 빠른 액션 링크 제공
 * - 반응형 디자인 지원
 *
 * [확장 가능성]
 * - 포인트 적립 예측 기능
 * - 개인화된 사용 패턴 분석
 * - 포인트 활용 추천 시스템
 * - 게임화 요소 추가 (레벨, 뱃지)
 * - 소셜 기능 (포인트 선물, 랭킹)
 */
class IndexController extends Controller
{
    use JWTAuthTrait;

    /**
     * 사용자 개인 포인트 관리 페이지
     */
    public function __invoke(Request $request)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $userUuid = $user->uuid ?? '';
        $userId = $user->id ?? 0;

        // 사용자 포인트 정보 조회
        $userPoint = null;
        $recentLogs = collect();
        $expiringPoints = collect();
        $statistics = [
            'balance' => 0,
            'total_earned' => 0,
            'total_used' => 0,
            'total_expired' => 0,
            'expiring_soon' => 0,
            'recent_earn' => 0,
            'recent_use' => 0
        ];

        if ($userUuid && $userId) {
            try {
                // 사용자 포인트 정보 조회 (user_id 우선, user_uuid 보조)
                $userPoint = DB::table('user_point')->where('user_id', $userId)->first();
                if (!$userPoint) {
                    $userPoint = DB::table('user_point')->where('user_uuid', $userUuid)->first();
                }

                // 포인트 계정이 없으면 생성
                if (!$userPoint) {
                    DB::table('user_point')->insert([
                        'user_id' => $userId,
                        'user_uuid' => $userUuid,
                        'balance' => 0,
                        'total_earned' => 0,
                        'total_used' => 0,
                        'total_expired' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $userPoint = DB::table('user_point')->where('user_uuid', $userUuid)->first();
                }

                // 최근 포인트 거래 내역 (최근 10개)
                $recentLogs = DB::table('user_point_log')
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();

                // 만료 예정 포인트 (30일 이내)
                $expiringPoints = DB::table('user_point_expiry')
                    ->where('user_id', $userId)
                    ->where('expired', false)
                    ->where('expires_at', '<=', now()->addDays(30))
                    ->orderBy('expires_at', 'asc')
                    ->get();

                // 통계 정보 계산
                $statistics = [
                    'balance' => $userPoint->balance ?? 0,
                    'total_earned' => $userPoint->total_earned ?? 0,
                    'total_used' => $userPoint->total_used ?? 0,
                    'total_expired' => $userPoint->total_expired ?? 0,
                    'expiring_soon' => $expiringPoints->sum('amount'),
                    'recent_earn' => DB::table('user_point_log')
                        ->where('user_id', $userId)
                        ->where('amount', '>', 0)
                        ->where('created_at', '>=', now()->subDays(30))
                        ->sum('amount'),
                    'recent_use' => DB::table('user_point_log')
                        ->where('user_id', $userId)
                        ->where('amount', '<', 0)
                        ->where('created_at', '>=', now()->subDays(30))
                        ->sum('amount')
                ];

            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
                \Log::warning('Point controller query failed', [
                    'user_uuid' => $userUuid,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('jiny-emoney::home.point.index', [
            'user' => $user,
            'userPoint' => $userPoint,
            'recentLogs' => $recentLogs,
            'expiringPoints' => $expiringPoints,
            'statistics' => $statistics,
        ]);
    }
}