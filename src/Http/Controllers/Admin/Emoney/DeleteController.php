<?php

namespace Jiny\AuthEmoney\Http\Controllers\Admin\Emoney;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 이머니 지갑 삭제 처리 컨트롤러
 *
 * 진입 경로:
 * Route::delete('/admin/auth/emoney/{id}') → DeleteController::__invoke()
 */
class DeleteController extends Controller
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

        $deleteConfig = $jsonConfig['delete'] ?? [];

        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.emoney.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '지갑이 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '지갑 삭제에 실패했습니다.',
            ],
        ];
    }

    /**
     * 지갑 삭제 처리
     */
    public function __invoke($id)
    {
        $wallet = \DB::table('user_emoney')->where('id', $id)->first();

        if (!$wallet) {
            return redirect()->route('admin.auth.emoney.index')
                ->with('error', '지갑을 찾을 수 없습니다.');
        }

        // 관련 거래 내역도 삭제 (또는 상태만 변경)
        \DB::table('user_emoney')->where('id', $id)->delete();

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
