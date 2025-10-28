<?php

namespace Jiny\Emoney\Http\Controllers\Emoney;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 이머니 출금 컨트롤러
 */
class WithdrawController extends Controller
{
    use JWTAuthTrait;

    /**
     * 사용자 이머니 출금 페이지
     */
    public function __invoke(Request $request)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $userUuid = $user->uuid ?? '';

        // 사용자 이머니 정보 조회
        $emoney = null;
        $bankAccounts = collect();

        if ($userUuid) {
            try {
                $emoney = DB::table('user_emoney')->where('user_uuid', $userUuid)->first();
                $rawBankAccounts = DB::table('user_emoney_bank')
                    ->where('user_id', $userUuid)
                    ->where('enable', '1')
                    ->orderBy('`default`', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                // 뷰에서 사용하는 프로퍼티명으로 매핑
                $bankAccounts = $rawBankAccounts->map(function ($account) {
                    $account->is_default = $account->default == '1';
                    $account->bank_name = $account->bank;
                    $account->account_number = $account->account;
                    $account->account_holder = $account->owner;
                    return $account;
                });

                // 최근 출금 기록 조회 (최근 5개)
                $recentWithdrawals = DB::table('user_emoney_withdrawals')
                    ->where('user_uuid', $userUuid)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
                \Log::warning('Withdraw controller query failed', [
                    'user_uuid' => $userUuid,
                    'error' => $e->getMessage()
                ]);
                $recentWithdrawals = collect();
            }
        } else {
            $recentWithdrawals = collect();
        }

        return view('jiny-emoney::home.withdraw.index', [
            'user' => $user,
            'emoney' => $emoney,
            'bankAccounts' => $bankAccounts,
            'recentWithdrawals' => $recentWithdrawals ?? collect(),
        ]);
    }
}