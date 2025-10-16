<?php

namespace Jiny\Auth\Emoney\Http\Controllers\Admin\Emoney;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 이머니 지갑 생성 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/admin/auth/emoney') → StoreController::__invoke()
 */
class StoreController extends Controller
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

        $storeConfig = $jsonConfig['store'] ?? [];

        $this->actions = [
            'validation' => $storeConfig['validation'] ?? [],
            'routes' => [
                'success' => $storeConfig['redirect']['success'] ?? 'admin.auth.emoney.index',
                'error' => $storeConfig['redirect']['error'] ?? 'admin.auth.emoney.create',
            ],
            'messages' => [
                'success' => $storeConfig['messages']['success'] ?? '지갑이 성공적으로 생성되었습니다.',
                'error' => $storeConfig['messages']['error'] ?? '지갑 생성에 실패했습니다.',
            ],
        ];
    }

    /**
     * 지갑 생성 처리
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'])
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_emoney')->insert([
            'user_id' => $request->user_id,
            'balance' => $request->balance ?? 0,
            'points' => $request->points ?? 0,
            'currency' => $request->currency ?? 'KRW',
            'status' => $request->status ?? 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
