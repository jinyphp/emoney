<?php

namespace Jiny\Auth\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Emoney\Models\UserPoint;
use Jiny\Auth\Emoney\Models\UserPointLog;
use Jiny\Auth\Emoney\Models\UserPointExpiry;

/**
 * 관리자 - 사용자 포인트 계정 삭제 컨트롤러
 */
class DestroyController extends Controller
{
    /**
     * 포인트 계정 삭제 (소프트 삭제 또는 완전 삭제)
     */
    public function __invoke(Request $request, $id)
    {
        $userPoint = UserPoint::findOrFail($id);

        $request->validate([
            'deletion_type' => 'required|in:soft,hard',
            'confirm_password' => 'required|string',
            'deletion_reason' => 'nullable|string|max:255',
        ]);

        // 비밀번호 확인 (현재 로그인한 관리자)
        if (!auth()->attempt([
            'email' => auth()->user()->email,
            'password' => $request->confirm_password
        ], false)) {
            return redirect()
                ->back()
                ->with('error', '관리자 비밀번호가 일치하지 않습니다.');
        }

        DB::beginTransaction();

        try {
            $userName = $userPoint->user->name ?? '알 수 없는 사용자';
            $userEmail = $userPoint->user->email ?? '';
            $balance = $userPoint->balance;

            if ($request->deletion_type === 'soft') {
                // 포인트 잔액을 0으로 만들고 삭제 로그 추가
                if ($balance > 0) {
                    $userPoint->adminAdjustment(
                        -$balance,
                        $request->deletion_reason ?: '관리자에 의한 계정 삭제',
                        auth()->id()
                    );
                }

                // 만료 예정 포인트도 제거
                UserPointExpiry::where('user_id', $userPoint->user_id)
                    ->where('expired', false)
                    ->update(['expired' => true]);

                $message = "사용자 {$userName}({$userEmail})의 포인트 계정이 초기화되었습니다. (잔액: {$balance} → 0)";

            } else {
                // 완전 삭제 (매우 위험한 작업)
                UserPointExpiry::where('user_id', $userPoint->user_id)->delete();
                UserPointLog::where('user_id', $userPoint->user_id)->delete();
                $userPoint->delete();

                $message = "사용자 {$userName}({$userEmail})의 포인트 계정과 모든 관련 데이터가 완전히 삭제되었습니다.";
            }

            DB::commit();

            return redirect()
                ->route('admin.auth.point.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', '포인트 계정 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}