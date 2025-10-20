<?php

namespace Jiny\Emoney\Http\Controllers\Admin\EmoneyBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Emoney\Models\UserEmoneyBank;

/**
 * 관리자 - 사용자 은행 계좌 상세보기 컨트롤러
 */
class ShowController extends Controller
{
    /**
     * 은행 계좌 상세 정보 표시
     */
    public function __invoke(Request $request, $id)
    {
        $bank = UserEmoneyBank::with('user')->findOrFail($id);

        // 같은 사용자의 다른 계좌들
        $otherBanks = UserEmoneyBank::where('user_id', $bank->user_id)
            ->where('id', '!=', $bank->id)
            ->orderBy('default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // 계좌 활동 로그 (향후 구현 예정)
        $activityLogs = [];

        return view('jiny-emoney::admin.emoney-bank.show', [
            'bank' => $bank,
            'otherBanks' => $otherBanks,
            'activityLogs' => $activityLogs,
        ]);
    }
}