<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Emoney;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 이머니 지갑 상세 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/emoney/{id}') → ShowController::__invoke()
 */
class ShowController extends Controller
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

        $showConfig = $jsonConfig['show'] ?? [];

        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-emoney::admin.emoney.show',
            'title' => $showConfig['title'] ?? '지갑 상세',
            'subtitle' => $showConfig['subtitle'] ?? '지갑 정보 및 거래 내역',
        ];
    }

    /**
     * 지갑 상세 정보 표시
     */
    public function __invoke($id)
    {
        $wallet = \DB::table('user_emoney')->where('id', $id)->first();

        if (!$wallet) {
            return redirect()->route('admin.auth.emoney.index')
                ->with('error', '지갑을 찾을 수 없습니다.');
        }

        // 사용자 정보
        $user = \App\Models\User::find($wallet->user_id);

        // 최근 거래 내역
        $transactions = \DB::table('emoney_transactions')
            ->where('wallet_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view($this->config['view'], compact('wallet', 'user', 'transactions'));
    }
}
