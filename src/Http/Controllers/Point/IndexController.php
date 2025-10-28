<?php

namespace Jiny\Emoney\Http\Controllers\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 포인트 관리 컨트롤러
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