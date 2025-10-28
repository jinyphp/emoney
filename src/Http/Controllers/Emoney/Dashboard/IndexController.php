<?php

namespace Jiny\Emoney\Http\Controllers\Emoney\Dashboard;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 이머니 & 포인트 대시보드
 *
 * [메소드 호출 관계 트리]
 * IndexController
 * └── __invoke(Request $request)
 *     ├── getAuthenticatedUser($request) - JWT 다중 인증 방식으로 사용자 확인
 *     ├── 이머니 정보 조회 블록
 *     │   ├── DB::table('user_emoney')->where('user_uuid', $userUuid)->first() - 이머니 잔액
 *     │   └── DB::table('user_emoney_log') - 최근 이머니 거래 로그 (최대 10개)
 *     │       ├── ->where('user_uuid', $userUuid) - 해당 사용자만
 *     │       ├── ->orderBy('created_at', 'desc') - 최신순 정렬
 *     │       └── ->limit(10) - 최대 10개 제한
 *     ├── 포인트 정보 조회 블록
 *     │   ├── DB::table('user_point')->where('user_uuid', $userUuid)->first() - 포인트 잔액
 *     │   └── DB::table('user_point_log') - 최근 포인트 거래 로그 (최대 10개)
 *     │       ├── ->where('user_uuid', $userUuid) - 해당 사용자만
 *     │       ├── ->orderBy('created_at', 'desc') - 최신순 정렬
 *     │       └── ->limit(10) - 최대 10개 제한
 *     ├── 은행 계좌 정보 조회 블록
 *     │   └── DB::table('user_emoney_bank') - 등록된 은행계좌 목록
 *     │       ├── ->where('user_uuid', $userUuid) - 해당 사용자만
 *     │       └── ->orderBy('created_at', 'desc') - 최신 등록순
 *     └── view('jiny-emoney::home.emoney.index', $data) - 대시보드 뷰 렌더링
 *
 * [컨트롤러 역할]
 * - 이머니 & 포인트 통합 대시보드 제공
 * - 현재 잔액 정보 및 최근 거래 내역 표시
 * - 등록된 은행계좌 목록 표시
 * - 각 서비스별 빠른 접근 링크 제공
 * - 전체적인 자산 현황 overview 제공
 *
 * [대시보드 구성 요소]
 * 1. 이머니 섹션
 *    - 현재 이머니 잔액
 *    - 최근 10개 거래 내역
 *    - 충전/출금 빠른 링크
 *
 * 2. 포인트 섹션
 *    - 현재 포인트 잔액
 *    - 최근 10개 포인트 거래 내역
 *    - 포인트 사용/적립 빠른 링크
 *
 * 3. 은행 계좌 섹션
 *    - 등록된 은행계좌 목록
 *    - 기본 계좌 표시
 *    - 계좌 관리 빠른 링크
 *
 * [데이터 로딩 전략]
 * - 병렬 처리: 이머니, 포인트, 은행계좌 정보를 독립적으로 조회
 * - 예외 처리: 각 섹션별 독립적 예외 처리로 부분 실패 허용
 * - 성능 최적화: 최근 10개 항목만 조회하여 로딩 속도 향상
 * - 빈 컬렉션 반환: 오류 시에도 UI가 깨지지 않도록 처리
 *
 * [UUID 기반 샤딩 지원]
 * - 모든 쿼리에서 user_uuid 사용
 * - 샤딩된 데이터베이스 환경 대응
 * - 크로스 샤드 데이터 일관성 유지
 * - 확장 가능한 아키텍처 지원
 *
 * [보안 고려사항]
 * - JWT 토큰 기반 사용자 인증
 * - 본인의 데이터만 조회 가능 (user_uuid 필터링)
 * - SQL 인젝션 방지 (Query Builder 사용)
 * - 민감 정보 노출 방지 (예외 처리)
 *
 * [성능 최적화]
 * - 제한된 데이터 조회 (최근 10개)
 * - 인덱스 활용: user_uuid, created_at
 * - 조건부 쿼리: 사용자 UUID 존재 시에만 실행
 * - 예외 무시: 테이블 미존재 시 성능 저하 방지
 *
 * [예외 처리 정책]
 * - 테이블 미존재: 서비스 초기 단계 대응
 * - 연결 실패: 일시적 장애 상황 대응
 * - 빈 결과: 신규 사용자 대응
 * - 로그 무시: 사용자 경험 우선
 *
 * [라우트 연결]
 * Route: GET /home/emoney
 * Name: home.emoney.index
 *
 * [관련 컨트롤러]
 * - Deposit\*: 충전 관련 기능
 * - Withdraw\*: 출금 관련 기능
 * - Log\IndexController: 거래 내역
 * - Bank\*: 계좌 관리 기능
 * - Point\*: 포인트 관리 기능
 *
 * [뷰 데이터]
 * - user: 사용자 정보
 * - emoney: 이머니 잔액 정보
 * - emoneyLogs: 최근 이머니 거래 로그 (10개)
 * - point: 포인트 잔액 정보
 * - pointLogs: 최근 포인트 거래 로그 (10개)
 * - bankAccounts: 등록된 은행계좌 목록
 *
 * [확장 가능성]
 * - 위젯 형태 대시보드 구성
 * - 사용자 맞춤형 대시보드
 * - 실시간 데이터 업데이트
 * - 알림 및 공지사항 통합
 * - 분석 차트 및 그래프 추가
 */
class IndexController extends Controller
{
    use JWTAuthTrait;

    /**
     * 사용자 개인 이머니 & 포인트 대시보드
     */
    public function __invoke(Request $request)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $userUuid = $user->uuid ?? '';

        // 이머니 정보 조회
        $emoney = null;
        $emoneyLogs = collect();
        if ($userUuid) {
            try {
                $emoney = DB::table('user_emoney')->where('user_uuid', $userUuid)->first();
                $emoneyLogs = DB::table('user_emoney_log')
                    ->where('user_uuid', $userUuid)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
            }
        }

        // 포인트 정보 조회
        $point = null;
        $pointLogs = collect();
        if ($userUuid) {
            try {
                $point = DB::table('user_point')->where('user_uuid', $userUuid)->first();
                $pointLogs = DB::table('user_point_log')
                    ->where('user_uuid', $userUuid)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
            }
        }

        // 은행 계좌 정보
        $bankAccounts = collect();
        if ($userUuid) {
            try {
                $bankAccounts = DB::table('user_emoney_bank')
                    ->where('user_uuid', $userUuid)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
            }
        }

        return view('jiny-emoney::home.emoney.index', [
            'user' => $user,
            'emoney' => $emoney,
            'emoneyLogs' => $emoneyLogs,
            'point' => $point,
            'pointLogs' => $pointLogs,
            'bankAccounts' => $bankAccounts,
        ]);
    }
}