<?php

namespace Jiny\Auth\Emoney\Http\Controllers\Emoney\Bank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\AuthEmoney\Models\UserEmoneyBank;

/**
 * 관리자 - 사용자 은행 계좌 삭제 컨트롤러
 */
class DestroyController extends Controller
{
    /**
     * 은행 계좌 삭제
     */
    public function __invoke(Request $request, $id)
    {
        $bank = UserEmoneyBank::findOrFail($id);

        try {
            // 기본 계좌인 경우 삭제 방지 (선택사항)
            if ($bank->default) {
                return redirect()->back()
                    ->withErrors(['error' => '기본 계좌는 삭제할 수 없습니다. 먼저 다른 계좌를 기본으로 설정해주세요.']);
            }

            // 연관된 거래 내역이 있는지 확인 (향후 구현 예정)
            // if ($bank->hasTransactions()) {
            //     return redirect()->back()
            //         ->withErrors(['error' => '거래 내역이 있는 계좌는 삭제할 수 없습니다.']);
            // }

            $userId = $bank->user_id;
            $bankName = $bank->bank;
            $account = $bank->account;

            $bank->delete();

            return redirect()->route('admin.auth.emoney.bank.index')
                ->with('success', "은행 계좌가 삭제되었습니다. (사용자: {$userId}, 은행: {$bankName}, 계좌: {$account})");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => '은행 계좌 삭제 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }
    }
}