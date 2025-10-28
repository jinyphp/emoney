<?php

namespace Jiny\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Jiny\Emoney\Models\AuthBank;

/**
 * 관리자 - 은행 저장 컨트롤러
 *
 * [메소드 호출 관계 트리]
 * StoreController
 * └── __invoke(Request $request)
 *     ├── Validator::make($request->all(), $rules, $messages) - 유효성 검사 실행
 *     ├── $validator->fails() - 유효성 검사 실패 확인
 *     ├── redirect()->back()->withErrors($validator)->withInput() - 실패 시 리다이렉트
 *     ├── AuthBank::where('country', $country)->max('sort_order') - 최대 정렬 순서 조회
 *     ├── AuthBank::create($data) - 새 은행 데이터 생성
 *     └── redirect()->route('admin.auth.bank.index')->with('success', $message) - 성공 시 리다이렉트
 *
 * [컨트롤러 역할]
 * - CreateController에서 전송된 은행 생성 폼 데이터를 처리
 * - 입력 데이터의 유효성 검사 및 중복 확인
 * - 정렬 순서 자동 설정 (비어있을 경우)
 * - 새로운 AuthBank 모델 인스턴스 생성 및 저장
 * - 성공/실패에 따른 적절한 리다이렉트 및 메시지 처리
 *
 * [유효성 검사 규칙]
 * - name: 필수, 최대 255자, 고유값
 * - code: 선택, 최대 10자, 고유값
 * - country: 필수, 정확히 2자 (ISO 국가 코드)
 * - swift_code: 선택, 최대 11자
 * - website: 선택, 유효한 URL, 최대 255자
 * - phone: 선택, 최대 50자
 * - account_number: 선택, 최대 50자
 * - account_holder: 선택, 최대 100자
 * - description: 선택, 최대 1000자
 * - enable: 불린값 (기본값: true)
 * - sort_order: 정수, 0-9999 범위
 *
 * [라우트 연결]
 * Route: POST /admin/auth/bank
 * Name: admin.auth.bank.store
 *
 * [관련 컨트롤러]
 * - CreateController: 생성 폼 제공
 * - IndexController: 저장 완료 후 목록으로 리다이렉트
 */
class StoreController extends Controller
{
    /**
     * 은행 정보 저장
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:auth_banks,name',
            'code' => 'nullable|string|max:10|unique:auth_banks,code',
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
            // 정렬 순서가 비어있으면 자동으로 설정
            $sortOrder = $request->input('sort_order');
            if (empty($sortOrder)) {
                $maxOrder = AuthBank::where('country', $request->input('country'))->max('sort_order');
                $sortOrder = ($maxOrder ?? 0) + 1;
            }

            AuthBank::create([
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'country' => $request->input('country'),
                'swift_code' => $request->input('swift_code'),
                'website' => $request->input('website'),
                'phone' => $request->input('phone'),
                'account_number' => $request->input('account_number'),
                'account_holder' => $request->input('account_holder'),
                'description' => $request->input('description'),
                'enable' => $request->boolean('enable', true),
                'sort_order' => $sortOrder,
            ]);

            return redirect()->route('admin.auth.bank.index')
                ->with('success', '은행이 성공적으로 등록되었습니다.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => '은행 등록 중 오류가 발생했습니다: ' . $e->getMessage()])
                ->withInput();
        }
    }
}