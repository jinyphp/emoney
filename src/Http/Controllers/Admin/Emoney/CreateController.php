<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Emoney;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 이머니 지갑 생성 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/emoney/create') → CreateController::__invoke()
 */
class CreateController extends Controller
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

        $createConfig = $jsonConfig['create'] ?? [];

        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-emoney::admin.emoney.create',
            'title' => $createConfig['title'] ?? '지갑 생성',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 지갑 추가',
        ];
    }

    /**
     * 지갑 생성 폼 표시
     */
    public function __invoke()
    {
        // 모든 사용자 목록 (지갑 없는 사용자만)
        $users = \App\Models\User::whereNotExists(function ($query) {
            $query->select(\DB::raw(1))
                ->from('user_emoney')
                ->whereRaw('user_emoney.user_id = users.id');
        })->get();

        return view($this->config['view'], compact('users'));
    }
}
