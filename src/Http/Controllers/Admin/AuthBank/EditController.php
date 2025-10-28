<?php

namespace Jiny\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Emoney\Models\AuthBank;

/**
 * 관리자 - 은행 수정 폼 컨트롤러
 *
 * [메소드 호출 관계 트리]
 * EditController
 * ├── __invoke(Request $request, $id)
 * │   ├── AuthBank::findOrFail($id) - 수정할 은행 레코드 조회
 * │   └── view('jiny-emoney::admin.auth-bank.edit', $data) - 수정 폼 뷰 렌더링
 * └── getBanksByCountry(Request $request, $countryCode)
 *     ├── file_exists($bankListPath) - 은행 목록 파일 존재 확인
 *     ├── file_get_contents($bankListPath) - JSON 파일 읽기
 *     ├── json_decode($content, true) - JSON 데이터 파싱
 *     └── response()->json($data) - AJAX 응답 반환
 *
 * [컨트롤러 역할]
 * - 기존 은행 정보를 수정하기 위한 폼 페이지 제공
 * - 현재 은행 데이터를 폼에 미리 채워서 표시
 * - 국가 변경 시 해당 국가의 은행 목록을 제공하는 AJAX API
 * - CreateController와 유사하지만 기존 데이터 수정 목적
 *
 * [라우트 연결]
 * Route: GET /admin/auth/bank/{id}/edit - 수정 폼 표시
 * Route: GET /admin/auth/bank/api/edit/banks/{countryCode} - AJAX API (수정용)
 * Name: admin.auth.bank.edit, admin.auth.bank.api.edit.banks
 *
 * [관련 컨트롤러]
 * - ShowController: 상세보기에서 수정으로 이동
 * - UpdateController: 수정된 데이터 저장 처리
 * - IndexController: 수정 취소 시 목록으로 돌아가기
 *
 * [외부 의존성]
 * - banklist.json: 국가별 은행 목록 데이터 파일
 *
 * [예외 처리]
 * - findOrFail() 사용으로 존재하지 않는 ID는 자동으로 404 처리
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

    /**
     * 국가별 은행 목록 조회 (AJAX API)
     */
    public function getBanksByCountry(Request $request, $countryCode)
    {
        $bankListPath = dirname(__DIR__, 5) . '/config/banklist.json';

        if (!file_exists($bankListPath)) {
            return response()->json(['error' => '은행 목록을 찾을 수 없습니다.'], 404);
        }

        $bankList = json_decode(file_get_contents($bankListPath), true);

        if (!isset($bankList[$countryCode])) {
            return response()->json(['banks' => []]);
        }

        return response()->json(['banks' => $bankList[$countryCode]]);
    }
}