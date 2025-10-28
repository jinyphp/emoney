<?php

namespace Jiny\Emoney\Http\Controllers\Bank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 기본 계좌 설정
 */
class SetDefaultController extends Controller
{
    use JWTAuthTrait;

    public function __invoke(Request $request, int $accountId)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $userUuid = $user->uuid ?? '';

        try {
            // 모든 계좌의 기본 설정 해제
            DB::table('user_emoney_bank')
                ->where('user_uuid', $userUuid)
                ->update(['is_default' => 0]);

            // 선택한 계좌를 기본으로 설정
            DB::table('user_emoney_bank')
                ->where('id', $accountId)
                ->where('user_uuid', $userUuid)
                ->update(['is_default' => 1]);

            return redirect()->route('home.emoney.bank.index')
                ->with('success', '기본 계좌가 설정되었습니다.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '계좌 설정 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}