<?php

namespace Jiny\Emoney\Http\Controllers\Admin\EmoneyBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Jiny\Emoney\Models\UserEmoneyBank;

/**
 * 관리자 - 사용자 은행 계좌 업데이트 컨트롤러
 */
class UpdateController extends Controller
{
    /**
     * 은행 계좌 정보 업데이트
     */
    public function __invoke(Request $request, $id)
    {
        $bank = UserEmoneyBank::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'bank' => 'required|string|max:100',
            'account' => 'required|string|max:50',
            'owner' => 'required|string|max:100',
            'type' => 'nullable|string|max:20',
            'currency' => 'required|string|max:10',
            'swift' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive,pending,rejected',
            'enable' => 'boolean',
            'default' => 'boolean',
        ], [
            'bank.required' => '은행명을 입력해주세요.',
            'account.required' => '계좌번호를 입력해주세요.',
            'owner.required' => '예금주명을 입력해주세요.',
            'currency.required' => '통화를 선택해주세요.',
            'status.required' => '상태를 선택해주세요.',
            'status.in' => '올바른 상태를 선택해주세요.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // 계좌번호가 변경된 경우 중복 확인
        if ($bank->account !== $request->account || $bank->bank !== $request->bank) {
            $existing = UserEmoneyBank::where('user_id', $bank->user_id)
                ->where('bank', $request->bank)
                ->where('account', $request->account)
                ->where('id', '!=', $bank->id)
                ->first();

            if ($existing) {
                return redirect()->back()
                    ->withErrors(['account' => '이미 등록된 계좌입니다.'])
                    ->withInput();
            }
        }

        try {
            // 기본 계좌로 설정하는 경우 기존 기본 계좌 해제
            if ($request->boolean('default') && !$bank->default) {
                UserEmoneyBank::where('user_id', $bank->user_id)
                    ->where('id', '!=', $bank->id)
                    ->update(['default' => false]);
            }

            // 은행 계좌 정보 업데이트
            $bank->update([
                'bank' => $request->bank,
                'account' => $request->account,
                'owner' => $request->owner,
                'type' => $request->type,
                'currency' => $request->currency,
                'swift' => $request->swift,
                'description' => $request->description,
                'status' => $request->status,
                'enable' => $request->boolean('enable'),
                'default' => $request->boolean('default'),
            ]);

            return redirect()->route('admin.auth.emoney.bank.show', $bank->id)
                ->with('success', '은행 계좌 정보가 성공적으로 업데이트되었습니다.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => '은행 계좌 업데이트 중 오류가 발생했습니다: ' . $e->getMessage()])
                ->withInput();
        }
    }
}