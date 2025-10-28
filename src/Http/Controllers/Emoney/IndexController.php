<?php

namespace Jiny\Emoney\Http\Controllers\Emoney;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 이머니 & 포인트 대시보드 컨트롤러
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
