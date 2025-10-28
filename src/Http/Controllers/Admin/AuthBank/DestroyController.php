<?php

namespace Jiny\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Emoney\Models\AuthBank;

/**
 * 관리자 - 은행 삭제 컨트롤러
 *
 * [메소드 호출 관계 트리]
 * DestroyController
 * └── __invoke(Request $request, $id)
 *     ├── AuthBank::findOrFail($id) - 삭제할 은행 레코드 조회
 *     ├── $bank->hasRelatedData() - 관련 데이터 존재 확인 (향후 구현 예정)
 *     ├── $bank->delete() - 은행 레코드 삭제
 *     └── redirect()->route('admin.auth.bank.index')->with('success', $message) - 목록으로 리다이렉트
 *
 * [컨트롤러 역할]
 * - 특정 은행 레코드의 영구 삭제 처리
 * - 삭제 전 관련 데이터 존재 여부 확인 (미래 기능)
 * - 안전한 삭제 처리 및 적절한 피드백 제공
 * - 삭제 완료 후 은행 목록 페이지로 리다이렉트
 *
 * [보안 및 안전성]
 * - 관련 데이터가 있는 경우 삭제 방지 (주석으로 향후 구현 예정)
 * - 예외 처리를 통한 안전한 삭제 작업
 * - 삭제된 은행명을 성공 메시지에 포함하여 명확한 피드백
 *
 * [라우트 연결]
 * Route: DELETE /admin/auth/bank/{id}
 * Name: admin.auth.bank.destroy
 *
 * [관련 컨트롤러]
 * - IndexController: 삭제 완료 후 목록으로 리다이렉트
 * - ShowController: 상세보기에서 삭제 버튼 클릭
 *
 * [향후 개선 사항]
 * - hasRelatedData() 메소드 구현으로 참조 무결성 확인
 * - 소프트 삭제(Soft Delete) 구현 고려
 * - 삭제 확인 로그 기록 추가
 *
 * [예외 처리]
 * - findOrFail() 사용으로 존재하지 않는 ID는 자동으로 404 처리
 * - 삭제 과정에서 발생하는 예외는 catch로 처리
 */
class DestroyController extends Controller
{
    /**
     * 은행 삭제
     */
    public function __invoke(Request $request, $id)
    {
        $bank = AuthBank::findOrFail($id);

        try {
            // 삭제 전 관련 데이터 확인 (향후 구현)
            // if ($bank->hasRelatedData()) {
            //     return redirect()->back()
            //         ->withErrors(['error' => '이 은행을 사용하는 데이터가 있어 삭제할 수 없습니다.']);
            // }

            $bankName = $bank->name;
            $bank->delete();

            return redirect()->route('admin.auth.bank.index')
                ->with('success', "은행 '{$bankName}'이(가) 삭제되었습니다.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => '은행 삭제 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }
    }
}