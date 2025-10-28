<?php

namespace Jiny\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Emoney\Models\AuthBank;

/**
 * 관리자 - 은행 상세보기 컨트롤러
 *
 * [메소드 호출 관계 트리]
 * ShowController
 * └── __invoke(Request $request, $id)
 *     ├── AuthBank::findOrFail($id) - 은행 ID로 단일 레코드 조회 (404 자동 처리)
 *     ├── AuthBank::where('country', $bank->country) - 동일 국가 은행 조회
 *     ├── $query->where('id', '!=', $bank->id) - 현재 은행 제외
 *     ├── $query->enabled() - 활성화된 은행만 필터링
 *     ├── $query->ordered() - 정렬 순서 적용
 *     ├── $query->limit(5)->get() - 최대 5개까지 조회
 *     └── view('jiny-emoney::admin.auth-bank.show', $data) - 상세보기 뷰 렌더링
 *
 * [컨트롤러 역할]
 * - 특정 은행의 상세 정보를 표시
 * - 동일 국가의 관련 은행들을 함께 제공
 * - 존재하지 않는 은행 ID에 대해 404 에러 자동 처리
 * - 관련 은행 추천 기능 제공
 *
 * [라우트 연결]
 * Route: GET /admin/auth/bank/{id}
 * Name: admin.auth.bank.show
 *
 * [관련 컨트롤러]
 * - IndexController: 목록에서 상세보기로 진입
 * - EditController: 상세보기에서 수정으로 이동
 * - DestroyController: 상세보기에서 삭제 가능
 *
 * [예외 처리]
 * - findOrFail() 사용으로 존재하지 않는 ID는 자동으로 404 ModelNotFoundException 발생
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