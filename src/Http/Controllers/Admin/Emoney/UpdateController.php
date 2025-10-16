<?php

namespace Jiny\AuthEmoney\Http\Controllers\Admin\Emoney;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 이머니 지갑 수정 처리 컨트롤러
 *
 * 진입 경로:
 * Route::put('/admin/auth/emoney/{id}') → UpdateController::__invoke()
 */
class UpdateController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->loadActions();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadActions()
    {
        $configPath = __DIR__ . '/Emoney.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $updateConfig = $jsonConfig['update'] ?? [];

        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.emoney.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.emoney.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '지갑 정보가 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '지갑 정보 업데이트에 실패했습니다.',
            ],
        ];
    }

    /**
     * 지갑 수정 처리
     */
    public function __invoke(Request $request, $id)
    {
        $wallet = \DB::table('user_emoney')->where('id', $id)->first();

        if (!$wallet) {
            return redirect()->route('admin.auth.emoney.index')
                ->with('error', '지갑을 찾을 수 없습니다.');
        }

        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_emoney')->where('id', $id)->update([
            'balance' => $request->balance,
            'points' => $request->points,
            'currency' => $request->currency,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}
