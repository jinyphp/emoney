<?php

namespace Jiny\Emoney\Http\Controllers\Emoney;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 이머니 거래 로그 컨트롤러
 */
class LogController extends Controller
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