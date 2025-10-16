<?php

namespace Jiny\Auth\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Emoney\Models\AuthBank;

/**
 * 관리자 - 은행 삭제 컨트롤러
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