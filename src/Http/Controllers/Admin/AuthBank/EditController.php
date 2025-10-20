<?php

namespace Jiny\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Emoney\Models\AuthBank;

/**
 * 관리자 - 은행 수정 폼 컨트롤러
 */
class EditController extends Controller
{
    /**
     * 은행 수정 폼 표시
     */
    public function __invoke(Request $request, $id)
    {
        $bank = AuthBank::findOrFail($id);

        // 국가 목록
        $countries = [
            'KR' => '대한민국',
            'US' => '미국',
            'JP' => '일본',
            'CN' => '중국',
            'GB' => '영국',
            'DE' => '독일',
            'FR' => '프랑스',
            'CA' => '캐나다',
            'AU' => '호주',
            'SG' => '싱가포르',
            'HK' => '홍콩',
            'TH' => '태국',
            'VN' => '베트남',
            'ID' => '인도네시아',
            'MY' => '말레이시아',
            'PH' => '필리핀',
        ];

        return view('jiny-emoney::admin.auth-bank.edit', [
            'bank' => $bank,
            'countries' => $countries,
        ]);
    }
}