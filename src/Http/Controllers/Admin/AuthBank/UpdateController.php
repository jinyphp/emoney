<?php

namespace Jiny\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Jiny\Emoney\Models\AuthBank;

/**
 * 관리자 - 은행 업데이트 컨트롤러
 */
class UpdateController extends Controller
{
    /**
     * 은행 정보 업데이트
     */
    public function __invoke(Request $request, $id)
    {
        $bank = AuthBank::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:auth_banks,name,' . $bank->id,
            'code' => 'nullable|string|max:10|unique:auth_banks,code,' . $bank->id,
            'country' => 'required|string|size:2',
            'swift_code' => 'nullable|string|max:11',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'enable' => 'boolean',
            'sort_order' => 'integer|min:0|max:9999',
        ], [
            'name.required' => '은행명을 입력해주세요.',
            'name.unique' => '이미 등록된 은행명입니다.',
            'code.unique' => '이미 등록된 은행 코드입니다.',
            'country.required' => '국가를 선택해주세요.',
            'country.size' => '올바른 국가 코드를 선택해주세요.',
            'swift_code.max' => 'SWIFT 코드는 11자리 이하로 입력해주세요.',
            'website.url' => '올바른 웹사이트 주소를 입력해주세요.',
            'sort_order.integer' => '정렬 순서는 숫자로 입력해주세요.',
            'sort_order.min' => '정렬 순서는 0 이상의 숫자를 입력해주세요.',
            'sort_order.max' => '정렬 순서는 9999 이하의 숫자를 입력해주세요.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $bank->update([
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'country' => $request->input('country'),
                'swift_code' => $request->input('swift_code'),
                'website' => $request->input('website'),
                'phone' => $request->input('phone'),
                'account_number' => $request->input('account_number'),
                'account_holder' => $request->input('account_holder'),
                'description' => $request->input('description'),
                'enable' => $request->boolean('enable'),
                'sort_order' => $request->input('sort_order', $bank->sort_order),
            ]);

            return redirect()->route('admin.auth.bank.show', $bank->id)
                ->with('success', '은행 정보가 성공적으로 업데이트되었습니다.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => '은행 정보 업데이트 중 오류가 발생했습니다: ' . $e->getMessage()])
                ->withInput();
        }
    }
}