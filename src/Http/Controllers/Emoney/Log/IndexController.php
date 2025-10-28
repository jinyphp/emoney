<?php

namespace Jiny\Emoney\Http\Controllers\Emoney\Log;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 이머니 거래 로그
 *
 * [메소드 호출 관계 트리]
 * IndexController
 * └── __invoke(Request $request)
 *     ├── getAuthenticatedUser($request) - JWT 다중 인증 방식으로 사용자 확인
 *     ├── DB::table('user_emoney_logs') - 이머니 거래 로그 조회
 *     │   ├── ->where('user_uuid', $userUuid) - 해당 사용자만
 *     │   ├── ->orderBy('created_at', 'desc') - 최신순 정렬
 *     │   └── ->paginate(20) - 페이지네이션 처리
 *     ├── DB::table('user_emoney')->where('user_uuid', $userUuid)->first() - 현재 이머니 정보
 *     └── view('jiny-emoney::home.emoney_log.index', $data) - 거래 로그 뷰 렌더링
 *
 * [컨트롤러 역할]
 * - 사용자의 이머니 거래 로그 조회 및 표시
 * - 입금, 출금, 사용, 환불 등 모든 이머니 거래 내역 표시
 * - 페이지네이션을 통한 대량 데이터 처리
 * - 현재 이머니 잔액 정보 표시
 * - 예외 처리로 안정적인 데이터 조회
 *
 * [데이터 조회 범위]
 * - 이머니 거래 로그: user_emoney_logs 테이블
 * - 현재 잔액 정보: user_emoney 테이블
 * - 사용자별 UUID 기반 필터링
 * - 최신 거래부터 역순 정렬
 *
 * [페이지네이션]
 * - 페이지당 20개 항목 표시
 * - Laravel 기본 페이지네이션 사용
 * - 성능 최적화를 위한 LIMIT/OFFSET 처리
 *
 * [보안 고려사항]
 * - JWT 토큰 기반 사용자 인증
 * - 본인의 거래 내역만 조회 가능
 * - SQL 인젝션 방지 (Query Builder 사용)
 * - 예외 처리로 시스템 정보 노출 방지
 *
 * [예외 처리]
 * - 테이블 존재 여부 확인
 * - 데이터베이스 연결 실패 대응
 * - 로그 기록을 통한 오류 추적
 * - 빈 컬렉션 반환으로 UI 깨짐 방지
 *
 * [라우트 연결]
 * Route: GET /emoney/log
 * Name: home.emoney.log
 *
 * [관련 컨트롤러]
 * - IndexController: 이머니 대시보드
 * - Deposit\*: 충전 관련 컨트롤러
 * - Withdraw\*: 출금 관련 컨트롤러
 *
 * [뷰 데이터]
 * - user: 사용자 정보
 * - emoneyLogs: 이머니 거래 로그 (페이지네이션)
 * - userEmoney: 현재 이머니 잔액 정보
 *
 * [성능 최적화]
 * - 인덱스 활용: user_uuid, created_at
 * - 페이지네이션으로 메모리 사용량 제한
 * - 조건부 조회로 불필요한 쿼리 방지
 * - 예외 처리로 무거운 연산 스킵
 */
class IndexController extends Controller
{
    use JWTAuthTrait;

    /**
     * 사용자 개인 이머니 거래 로그 목록
     */
    public function __invoke(Request $request)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $userUuid = $user->uuid ?? '';

        // 사용자의 이머니 거래 내역 조회
        $emoneyLogs = collect();
        $pointLogs = collect();

        if ($userUuid) {
            try {
                // 이머니 로그 조회 (수정된 테이블명)
                $emoneyLogs = DB::table('user_emoney_logs')
                    ->where('user_uuid', $userUuid)
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);

                // 사용자 이머니 정보 조회
                $userEmoney = DB::table('user_emoney')->where('user_uuid', $userUuid)->first();

            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
                \Log::warning('Emoney log query failed', [
                    'user_uuid' => $userUuid,
                    'error' => $e->getMessage()
                ]);
                $emoneyLogs = collect();
                $userEmoney = null;
            }
        }

        return view('jiny-emoney::home.emoney_log.index', [
            'user' => $user,
            'emoneyLogs' => $emoneyLogs,
            'userEmoney' => $userEmoney,
        ]);
    }
}