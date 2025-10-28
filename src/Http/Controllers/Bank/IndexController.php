<?php

namespace Jiny\Emoney\Http\Controllers\Bank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 은행 계좌 관리 컨트롤러
 */
class IndexController extends Controller
{
    use JWTAuthTrait;

    /**
     * 사용자 개인 은행 계좌 관리 페이지
     */
    public function __invoke(Request $request)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $userUuid = $user->uuid ?? '';

        // 사용자 은행 계좌 목록 조회
        $bankAccounts = collect();

        if ($userUuid) {
            try {
                $rawAccounts = DB::table('user_emoney_bank')
                    ->where('user_id', $userUuid)
                    ->where('enable', '1')
                    ->orderBy('`default`', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                // 뷰에서 사용하는 프로퍼티명으로 매핑
                $bankAccounts = $rawAccounts->map(function ($account) {
                    $account->is_default = $account->default == '1';
                    $account->bank_name = $account->bank;
                    $account->account_number = $account->account;
                    $account->account_holder = $account->owner;

                    // JSON 디스크립션 파싱
                    if ($account->description) {
                        try {
                            $description = json_decode($account->description, true);
                            $account->country = $description['country'] ?? '';
                            $account->bank_code = $description['bank_code'] ?? '';
                        } catch (\Exception $e) {
                            $account->country = '';
                            $account->bank_code = '';
                        }
                    }

                    return $account;
                });
            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
                \Log::warning('Bank accounts query failed', [
                    'user_uuid' => $userUuid,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('jiny-emoney::home.bank.index', [
            'user' => $user,
            'bankAccounts' => $bankAccounts,
        ]);
    }
}