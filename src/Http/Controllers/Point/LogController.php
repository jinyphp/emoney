<?php

namespace Jiny\Emoney\Http\Controllers\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 포인트 로그 컨트롤러
 */
class LogController extends Controller
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