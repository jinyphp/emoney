<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Emoney;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 이머니 지갑 수정 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/emoney/{id}/edit') → EditController::__invoke()
 */
class EditController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadConfig()
    {
        $configPath = __DIR__ . '/Emoney.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $editConfig = $jsonConfig['edit'] ?? [];

        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-emoney::admin.emoney.edit',
            'title' => $editConfig['title'] ?? '지갑 수정',
            'subtitle' => $editConfig['subtitle'] ?? '지갑 정보 수정',
        ];
    }

    /**
     * 지갑 수정 폼 표시
     */
    public function __invoke($id)
    {
        $wallet = \DB::table('user_emoney')->where('id', $id)->first();

        if (!$wallet) {
            return redirect()->route('admin.auth.emoney.index')
                ->with('error', '지갑을 찾을 수 없습니다.');
        }

        $user = \App\Models\User::find($wallet->user_id);

        return view($this->config['view'], compact('wallet', 'user'));
    }
}
