<?php

namespace Jiny\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

/**
 * 관리자 - 은행 생성 폼 컨트롤러
 *
 * [메소드 호출 관계 트리]
 * CreateController
 * ├── __invoke(Request $request)
 * │   └── view('jiny-emoney::admin.auth-bank.create', $data) - 생성 폼 뷰 렌더링
 * └── getBanksByCountry(Request $request, $countryCode)
 *     ├── file_exists($bankListPath) - 은행 목록 파일 존재 확인
 *     ├── file_get_contents($bankListPath) - JSON 파일 읽기
 *     ├── json_decode($content, true) - JSON 데이터 파싱
 *     ├── json_last_error() - JSON 파싱 오류 확인
 *     └── response()->json($data) - AJAX 응답 반환
 *
 * [컨트롤러 역할]
 * - 새로운 은행 생성을 위한 폼 페이지 제공
 * - 국가별 은행 목록을 제공하는 AJAX API 엔드포인트
 * - 클라이언트 사이드에서 국가 선택 시 은행 목록 동적 로딩 지원
 *
 * [라우트 연결]
 * Route: GET /admin/auth/bank/create - 생성 폼 표시
 * Route: GET /admin/auth/bank/api/banks/{countryCode} - AJAX API
 * Name: admin.auth.bank.create, admin.auth.bank.api.banks
 *
 * [관련 컨트롤러]
 * - IndexController: 은행 목록으로 돌아가기
 * - StoreController: 생성된 데이터 저장 처리
 *
 * [외부 의존성]
 * - banklist.json: 국가별 은행 목록 데이터 파일
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

        $bankListPath = dirname(__DIR__, 5) . '/config/banklist.json';
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