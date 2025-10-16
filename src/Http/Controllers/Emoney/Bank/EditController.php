<?php

namespace Jiny\Auth\Emoney\Http\Controllers\Emoney\Bank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Emoney\Models\UserEmoneyBank;
use Jiny\Auth\Emoney\Models\AuthBank;

/**
 * 관리자 - 사용자 은행 계좌 수정 폼 컨트롤러
 */
class EditController extends Controller
{
    /**
     * 은행 계좌 수정 폼 표시
     */
    public function __invoke(Request $request, $id)
    {
        $bank = UserEmoneyBank::with('user')->findOrFail($id);

        // 은행 목록 (auth_banks 테이블에서 활성화된 은행들 조회)
        $banks = AuthBank::getSelectOptions();

        // 통화 목록
        $currencies = [
            'KRW' => '원화 (KRW)',
            'USD' => '달러 (USD)',
            'EUR' => '유로 (EUR)',
            'JPY' => '엔화 (JPY)',
            'CNY' => '위안화 (CNY)',
        ];

        // 상태 목록
        $statuses = [
            'active' => '활성',
            'inactive' => '비활성',
            'pending' => '승인대기',
            'rejected' => '거부',
        ];

        return view('jiny-emoney::emoney.bank.edit', [
            'bank' => $bank,
            'banks' => $banks,
            'currencies' => $currencies,
            'statuses' => $statuses,
        ]);
    }
}