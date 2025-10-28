<?php

namespace Jiny\Emoney\Http\Controllers\Emoney\Deposit;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;
use Jiny\Auth\Facades\JwtAuth;

/**
 * 사용자 - 이머니 충전 페이지 표시
 */
class IndexController extends Controller
{
    use JWTAuthTrait;

    /**
     * 사용자 이머니 충전 페이지
     */
    public function __invoke(Request $request)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인 (파사드 사용)
        $user = JwtAuth::user($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $userUuid = $user->uuid ?? '';

        // 사용자 이머니 정보 가져오기
        $userEmoney = DB::table('user_emoney')
            ->where('user_uuid', $userUuid)
            ->first();

        // 이머니 잔액 확인
        $currentBalance = $userEmoney->balance ?? 0;

        // 사용자 등록된 은행 계좌 목록 (user_emoney_bank)
        $userBankAccounts = DB::table('user_emoney_bank')
            ->where('user_uuid', $userUuid)
            ->where('enable', true)
            ->get();

        // 입금 가능한 시스템 계좌 목록 (auth_banks)
        $depositBanks = DB::table('auth_banks')
            ->where('enable', true)
            ->where('country', 'KR')
            ->orderBy('sort_order')
            ->get();

        // 최근 충전 신청 내역 (최근 5건)
        $recentDeposits = DB::table('user_emoney_deposits')
            ->where('user_uuid', $userUuid)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // AJAX 요청인 경우 JSON 응답
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'currentBalance' => $currentBalance,
                'recentDeposits' => $recentDeposits,
                'userBankAccounts' => $userBankAccounts,
                'depositBanks' => $depositBanks
            ]);
        }

        return view('jiny-emoney::home.deposit.index', [
            'user' => $user,
            'currentBalance' => $currentBalance,
            'userBankAccounts' => $userBankAccounts,
            'depositBanks' => $depositBanks,
            'recentDeposits' => $recentDeposits,
        ]);
    }
}