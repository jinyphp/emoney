<?php

namespace Jiny\Emoney\Http\Controllers\Bank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 은행 계좌 수정 폼
 */
class EditController extends Controller
{
    use JWTAuthTrait;

    public function __invoke(Request $request, int $accountId)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        // 임시로 리다이렉트만 처리
        return redirect()->route('home.emoney.bank.index')
            ->with('info', '계좌 수정 기능은 개발 중입니다.');
    }
}