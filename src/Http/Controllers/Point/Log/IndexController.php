<?php

namespace Jiny\Emoney\Http\Controllers\Point\Log;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 포인트 거래 로그
 *
 * [메소드 호출 관계 트리]
 * IndexController
 * └── __invoke(Request $request)
 *     ├── getAuthenticatedUser($request) - JWT 다중 인증 방식으로 사용자 확인
 *     ├── DB::table('user_point')->where('user_uuid', $userUuid)->first() - 사용자 포인트 정보
 *     ├── 포인트 로그 쿼리 구성
 *     │   ├── DB::table('user_point_log')->where('user_uuid', $userUuid) - 기본 쿼리
 *     │   ├── ->where('transaction_type', $request->type) - 거래 유형 필터 (선택)
 *     │   ├── ->whereDate('created_at', '>=', $request->date_from) - 시작일 필터 (선택)
 *     │   ├── ->whereDate('created_at', '<=', $request->date_to) - 종료일 필터 (선택)
 *     │   ├── ->orderBy('created_at', 'desc') - 최신순 정렬
 *     │   └── ->paginate($perPage) - 페이지네이션 처리
 *     ├── 통계 정보 계산
 *     │   ├── total_logs - 총 거래 건수
 *     │   ├── total_earned - 총 적립 포인트 (양수 금액 합계)
 *     │   ├── total_used - 총 사용 포인트 (음수 금액 합계)
 *     │   ├── recent_earn - 최근 30일 적립 포인트
 *     │   └── recent_use - 최근 30일 사용 포인트
 *     └── view('jiny-emoney::home.point.log', $data) - 포인트 로그 뷰 렌더링
 *
 * [컨트롤러 역할]
 * - 사용자의 포인트 거래 로그 조회 및 표시
 * - 포인트 적립, 사용, 환불, 만료 등 모든 거래 내역 표시
 * - 거래 유형별, 날짜별 필터링 기능 제공
 * - 포인트 사용 통계 및 요약 정보 제공
 * - 페이지네이션을 통한 대량 데이터 처리
 *
 * [필터링 기능]
 * - 거래 유형 필터: earn, use, refund, expire, admin
 * - 날짜 범위 필터: 시작일부터 종료일까지
 * - 페이지당 표시 개수: 기본 20개, 사용자 설정 가능
 *
 * [통계 정보]
 * - total_logs: 총 거래 건수
 * - total_earned: 총 적립 포인트 (lifetime)
 * - total_used: 총 사용 포인트 (lifetime, 음수값)
 * - recent_earn: 최근 30일 적립 포인트
 * - recent_use: 최근 30일 사용 포인트 (음수값)
 *
 * [거래 유형 (transaction_type)]
 * - earn: 포인트 적립 (구매, 리뷰, 이벤트 등)
 * - use: 포인트 사용 (할인, 상품 교환 등)
 * - refund: 포인트 환불 (주문 취소, 반품 등)
 * - expire: 포인트 만료 (유효기간 만료)
 * - admin: 관리자 조정 (수동 지급/차감)
 *
 * [금액 표기]
 * - 적립: 양수 금액 (+1000)
 * - 사용: 음수 금액 (-500)
 * - 환불: 양수 금액 (+200)
 * - 만료: 음수 금액 (-100)
 * - 관리자 조정: 상황에 따라 양수/음수
 *
 * [성능 최적화]
 * - 인덱스 활용: user_uuid, transaction_type, created_at
 * - 페이지네이션으로 메모리 사용량 제한
 * - 통계 쿼리 최적화 (집계 함수 활용)
 * - 조건부 필터링으로 불필요한 연산 방지
 *
 * [보안 고려사항]
 * - JWT 토큰 기반 사용자 인증
 * - 본인의 포인트 거래 내역만 조회 가능
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
 * Route: GET /point/log
 * Name: home.emoney.point.log
 *
 * [관련 컨트롤러]
 * - Point\IndexController: 포인트 대시보드
 * - Point\Expiry\IndexController: 포인트 만료 관리
 *
 * [뷰 데이터]
 * - user: 사용자 정보
 * - userPoint: 현재 포인트 잔액 정보
 * - pointLogs: 포인트 거래 로그 (페이지네이션)
 * - statistics: 포인트 사용 통계 정보
 *
 * [데이터 구조]
 * - user_point: 사용자 포인트 잔액 및 총계
 * - user_point_log: 개별 거래 로그 기록
 * - 샤딩 지원: user_uuid 기반 분산 처리
 *
 * [사용자 경험]
 * - 직관적인 필터링 인터페이스
 * - 명확한 거래 유형 표시
 * - 시각적 통계 정보 제공
 * - 빠른 페이지 로딩
 */
class IndexController extends Controller
{
    use JWTAuthTrait;

    /**
     * 사용자 개인 포인트 거래 로그
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
        $pointLogs = collect();
        $statistics = [
            'total_logs' => 0,
            'total_earned' => 0,
            'total_used' => 0,
            'recent_earn' => 0,
            'recent_use' => 0
        ];

        if ($userUuid && $userId) {
            try {
                // 사용자 포인트 정보 조회
                $userPoint = DB::table('user_point')->where('user_uuid', $userUuid)->first();

                // 필터링 조건 설정
                $query = DB::table('user_point_log')->where('user_uuid', $userUuid);

                // 거래 유형 필터
                if ($request->filled('type')) {
                    $query->where('transaction_type', $request->type);
                }

                // 날짜 범위 필터
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }

                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }

                // 페이지당 항목 수
                $perPage = $request->get('per_page', 20);

                $pointLogs = $query->orderBy('created_at', 'desc')->paginate($perPage);

                // 통계 계산
                $statistics = [
                    'total_logs' => DB::table('user_point_log')->where('user_uuid', $userUuid)->count(),
                    'total_earned' => DB::table('user_point_log')
                        ->where('user_uuid', $userUuid)
                        ->where('amount', '>', 0)
                        ->sum('amount'),
                    'total_used' => DB::table('user_point_log')
                        ->where('user_uuid', $userUuid)
                        ->where('amount', '<', 0)
                        ->sum('amount'),
                    'recent_earn' => DB::table('user_point_log')
                        ->where('user_uuid', $userUuid)
                        ->where('amount', '>', 0)
                        ->where('created_at', '>=', now()->subDays(30))
                        ->sum('amount'),
                    'recent_use' => DB::table('user_point_log')
                        ->where('user_uuid', $userUuid)
                        ->where('amount', '<', 0)
                        ->where('created_at', '>=', now()->subDays(30))
                        ->sum('amount')
                ];

            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
                \Log::warning('Point log controller query failed', [
                    'user_uuid' => $userUuid,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('jiny-emoney::home.point.log', [
            'user' => $user,
            'userPoint' => $userPoint,
            'pointLogs' => $pointLogs,
            'statistics' => $statistics,
        ]);
    }
}