<?php

namespace Jiny\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

/**
 * 관리자 - 은행 생성 폼 컨트롤러
 */
class CreateController extends Controller
{
    /**
     * 은행 생성 폼 표시
     */
    public function __invoke(Request $request)
    {
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

        return view('jiny-emoney::admin.auth-bank.create', [
            'countries' => $countries,
        ]);
    }

    /**
     * 국가별 은행 목록 조회 (AJAX API)
     */
    public function getBanksByCountry(Request $request, $countryCode)
    {
        \Log::info('getBanksByCountry called with country: ' . $countryCode);

        $bankListPath = __DIR__ . '/banklist.json';
        \Log::info('Looking for file at: ' . $bankListPath);

        if (!file_exists($bankListPath)) {
            \Log::error('Bank list file not found at: ' . $bankListPath);
            return response()->json(['error' => '은행 목록을 찾을 수 없습니다.'], 404);
        }

        $bankList = json_decode(file_get_contents($bankListPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('JSON decode error: ' . json_last_error_msg());
            return response()->json(['error' => 'JSON 파싱 오류가 발생했습니다.'], 500);
        }

        if (!isset($bankList[$countryCode])) {
            \Log::info('No banks found for country: ' . $countryCode);
            return response()->json(['banks' => []]);
        }

        \Log::info('Found ' . count($bankList[$countryCode]) . ' banks for country: ' . $countryCode);
        return response()->json(['banks' => $bankList[$countryCode]]);
    }
}