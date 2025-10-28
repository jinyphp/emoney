<?php

namespace Jiny\Emoney\Http\Controllers\Bank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 은행 계좌 등록 폼
 */
class CreateController extends Controller
{
    use JWTAuthTrait;

    public function __invoke(Request $request)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        return view('jiny-emoney::home.bank.create', [
            'user' => $user,
        ]);
    }
}