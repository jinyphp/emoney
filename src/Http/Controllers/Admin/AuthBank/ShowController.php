<?php

namespace Jiny\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Emoney\Models\AuthBank;

/**
 * 관리자 - 은행 상세보기 컨트롤러
 */
class ShowController extends Controller
{
    /**
     * 은행 상세 정보 표시
     */
    public function __invoke(Request $request, $id)
    {
        $bank = AuthBank::findOrFail($id);

        // 같은 국가의 다른 은행들
        $relatedBanks = AuthBank::where('country', $bank->country)
            ->where('id', '!=', $bank->id)
            ->enabled()
            ->ordered()
            ->limit(5)
            ->get();

        return view('jiny-emoney::admin.auth-bank.show', [
            'bank' => $bank,
            'relatedBanks' => $relatedBanks,
        ]);
    }
}