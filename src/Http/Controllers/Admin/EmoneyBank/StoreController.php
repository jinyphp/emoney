<?php

namespace Jiny\Auth\Emoney\Http\Controllers\Admin\EmoneyBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Jiny\AuthEmoney\Models\UserEmoneyBank;
use App\Models\User;

/**
 * 관리자 - 사용자 은행 계좌 저장 컨트롤러
 */
class StoreController extends Controller
{
    /**
     * 은행 계좌 정보 저장
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'email' => 'required|email',
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
            'user_id.required' => '사용자를 선택해주세요.',
            'user_id.integer' => '올바른 사용자 ID가 아닙니다.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식이 아닙니다.',
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

        // 사용자 정보 확인 (샤딩된 테이블에서 검색)
        $user = null;
        $tables = ['users', 'users_001', 'users_002'];

        foreach ($tables as $table) {
            try {
                $user = DB::table($table)
                    ->where('id', $request->user_id)
                    ->where('email', $request->email)
                    ->first();

                if ($user) {
                    break;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if (!$user) {
            return redirect()->back()
                ->withErrors(['user_id' => '사용자 정보가 일치하지 않습니다.'])
                ->withInput();
        }

        // 같은 계좌가 이미 등록되어 있는지 확인
        $existing = UserEmoneyBank::where('user_id', $request->user_id)
            ->where('bank', $request->bank)
            ->where('account', $request->account)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withErrors(['account' => '이미 등록된 계좌입니다.'])
                ->withInput();
        }

        try {
            // 기본 계좌로 설정하는 경우 기존 기본 계좌 해제
            if ($request->boolean('default')) {
                UserEmoneyBank::where('user_id', $request->user_id)
                    ->update(['default' => false]);
            }

            // 은행 계좌 생성
            $bank = UserEmoneyBank::create([
                'user_id' => $request->user_id,
                'email' => $request->email,
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

            return redirect()->route('admin.auth.emoney.bank.index')
                ->with('success', '은행 계좌가 성공적으로 등록되었습니다.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => '은행 계좌 등록 중 오류가 발생했습니다: ' . $e->getMessage()])
                ->withInput();
        }
    }
}