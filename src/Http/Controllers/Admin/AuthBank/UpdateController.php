<?php

namespace Jiny\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Jiny\Emoney\Models\AuthBank;

/**
 * 관리자 - 은행 업데이트 컨트롤러
 *
 * [메소드 호출 관계 트리]
 * UpdateController
 * └── __invoke(Request $request, $id)
 *     ├── AuthBank::findOrFail($id) - 업데이트할 은행 레코드 조회
 *     ├── Validator::make($request->all(), $rules, $messages) - 유효성 검사 실행
 *     ├── $validator->fails() - 유효성 검사 실패 확인
 *     ├── redirect()->back()->withErrors($validator)->withInput() - 실패 시 리다이렉트
 *     ├── $bank->update($data) - 기존 레코드 업데이트
 *     └── redirect()->route('admin.auth.bank.show', $bank->id)->with('success', $message) - 성공 시 리다이렉트
 *
 * [컨트롤러 역할]
 * - EditController에서 전송된 은행 수정 폼 데이터를 처리
 * - 기존 은행 레코드의 업데이트 수행
 * - StoreController와 유사하지만 기존 레코드 제외하고 중복 검사
 * - 성공 시 해당 은행의 상세보기 페이지로 리다이렉트
 *
 * [유효성 검사 규칙]
 * - name: 필수, 최대 255자, 고유값 (현재 레코드 제외)
 * - code: 선택, 최대 10자, 고유값 (현재 레코드 제외)
 * - country: 필수, 정확히 2자 (ISO 국가 코드)
 * - swift_code: 선택, 최대 11자
 * - website: 선택, 유효한 URL, 최대 255자
 * - phone: 선택, 최대 50자
 * - account_number: 선택, 최대 50자
 * - account_holder: 선택, 최대 100자
 * - description: 선택, 최대 1000자
 * - enable: 불린값
 * - sort_order: 정수, 0-9999 범위 (기본값: 기존 값 유지)
 *
 * [라우트 연결]
 * Route: PUT /admin/auth/bank/{id}
 * Name: admin.auth.bank.update
 *
 * [관련 컨트롤러]
 * - EditController: 수정 폼 제공
 * - ShowController: 업데이트 완료 후 상세보기로 리다이렉트
 *
 * [예외 처리]
 * - findOrFail() 사용으로 존재하지 않는 ID는 자동으로 404 처리
 * - 업데이트 과정에서 발생하는 예외는 catch로 처리
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