<?php

namespace Jiny\Emoney\Http\Controllers\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 포인트 만료 관리 컨트롤러
 */
class ExpiryController extends Controller
{
    use JWTAuthTrait;

    /**
     * 사용자 개인 포인트 만료 관리 페이지
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

        // 포인트 만료 정보 조회
        $pointExpiry = collect();
        $expiredPoints = collect();

        if ($userId) {
            try {
                // 만료 예정 포인트 (user_id 우선 조회)
                $pointExpiry = DB::table('user_point_expiry')
                    ->where('user_id', $userId)
                    ->where('expired', 0)
                    ->where('expires_at', '>', now())
                    ->orderBy('expires_at', 'asc')
                    ->paginate(20);

                // 이미 만료된 포인트 (최근 3개월)
                $expiredPoints = DB::table('user_point_expiry')
                    ->where('user_id', $userId)
                    ->where('expired', 1)
                    ->where('expires_at', '<=', now())
                    ->where('expires_at', '>=', now()->subMonths(3))
                    ->orderBy('expires_at', 'desc')
                    ->limit(10)
                    ->get();
            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
            }
        }

        return view('jiny-emoney::home.point.expiry', [
            'user' => $user,
            'pointExpiry' => $pointExpiry,
            'expiredPoints' => $expiredPoints,
        ]);
    }
}