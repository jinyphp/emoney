<?php

namespace Jiny\Emoney\Http\Controllers\Bank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 은행 계좌 삭제
 */
class DeleteController extends Controller
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
            // 계좌 삭제
            $deleted = DB::table('user_emoney_bank')
                ->where('id', $accountId)
                ->where('user_uuid', $userUuid)
                ->delete();

            if ($deleted) {
                return redirect()->route('home.emoney.bank.index')
                    ->with('success', '계좌가 삭제되었습니다.');
            } else {
                return redirect()->route('home.emoney.bank.index')
                    ->with('error', '계좌를 찾을 수 없습니다.');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '계좌 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}