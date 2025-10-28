<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Setting;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

/**
 * 관리자 - Emoney 설정 관리 컨트롤러
 */
class IndexController extends Controller
{
    private $settingPath;

    public function __construct()
    {
        $this->settingPath = base_path('vendor/jiny/emoney/config/setting.json');
    }

    /**
     * 설정 페이지 표시
     */
    public function index()
    {
        $settings = $this->loadSettings();

        return view('jiny-emoney::admin.setting.index', [
            'settings' => $settings,
        ]);
    }

    /**
     * 설정 저장
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'emoney.enable' => 'required|boolean',
                'emoney.register' => 'required|numeric|min:0',
                'point.enable' => 'required|boolean',
                'point.register' => 'required|numeric|min:0',
            ]);

            $settings = [
                'emoney' => [
                    'enable' => $request->boolean('emoney.enable'),
                    'register' => (int) $request->input('emoney.register', 0),
                ],
                'point' => [
                    'enable' => $request->boolean('point.enable'),
                    'register' => (int) $request->input('point.register', 0),
                ],
            ];

            // 추가 설정들
            if ($request->filled('emoney.default_balance')) {
                $settings['emoney']['default_balance'] = (int) $request->input('emoney.default_balance', 0);
            }

            if ($request->filled('emoney.max_balance')) {
                $settings['emoney']['max_balance'] = (int) $request->input('emoney.max_balance', 0);
            }

            if ($request->filled('emoney.transfer_enabled')) {
                $settings['emoney']['transfer_enabled'] = $request->boolean('emoney.transfer_enabled');
            }

            if ($request->filled('emoney.transfer_fee')) {
                $settings['emoney']['transfer_fee'] = (float) $request->input('emoney.transfer_fee', 0);
            }

            if ($request->filled('point.expiry_days')) {
                $settings['point']['expiry_days'] = (int) $request->input('point.expiry_days', 0);
            }

            if ($request->filled('point.expiry_enabled')) {
                $settings['point']['expiry_enabled'] = $request->boolean('point.expiry_enabled');
            }

            if ($request->filled('point.notification_days')) {
                $settings['point']['notification_days'] = (int) $request->input('point.notification_days', 7);
            }

            $this->saveSettings($settings);

            return redirect()
                ->route('admin.auth.emoney.setting.index')
                ->with('success', '설정이 성공적으로 저장되었습니다.');

        } catch (\Exception $e) {
            \Log::error('Emoney setting save error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', '설정 저장 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 설정 파일 로드
     */
    private function loadSettings()
    {
        try {
            if (!file_exists($this->settingPath)) {
                return $this->getDefaultSettings();
            }

            $content = file_get_contents($this->settingPath);
            $settings = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::warning('Invalid JSON in setting file', [
                    'path' => $this->settingPath,
                    'error' => json_last_error_msg()
                ]);
                return $this->getDefaultSettings();
            }

            // 기본값과 병합 (recursive 대신 단순 merge 사용)
            $defaultSettings = $this->getDefaultSettings();

            // 각 섹션별로 병합
            $mergedSettings = [];
            foreach ($defaultSettings as $section => $defaults) {
                $mergedSettings[$section] = array_merge($defaults, $settings[$section] ?? []);
            }

            return $mergedSettings;

        } catch (\Exception $e) {
            \Log::error('Setting file load error', [
                'path' => $this->settingPath,
                'error' => $e->getMessage()
            ]);

            return $this->getDefaultSettings();
        }
    }

    /**
     * 설정 파일 저장
     */
    private function saveSettings($settings)
    {
        $configDir = dirname($this->settingPath);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON 인코딩 오류: ' . json_last_error_msg());
        }

        if (file_put_contents($this->settingPath, $json) === false) {
            throw new \Exception('설정 파일 쓰기 실패');
        }

        // 파일 권한 설정
        chmod($this->settingPath, 0644);
    }

    /**
     * 기본 설정값
     */
    private function getDefaultSettings()
    {
        return [
            'emoney' => [
                'enable' => true,
                'register' => 1000,
                'default_balance' => 0,
                'max_balance' => 1000000,
                'transfer_enabled' => true,
                'transfer_fee' => 0,
            ],
            'point' => [
                'enable' => true,
                'register' => 1000,
                'expiry_enabled' => false,
                'expiry_days' => 365,
                'notification_days' => 7,
            ],
        ];
    }

    /**
     * 설정 초기화
     */
    public function reset()
    {
        try {
            $defaultSettings = $this->getDefaultSettings();
            $this->saveSettings($defaultSettings);

            return redirect()
                ->route('admin.auth.emoney.setting.index')
                ->with('success', '설정이 초기화되었습니다.');

        } catch (\Exception $e) {
            \Log::error('Setting reset error', [
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->with('error', '설정 초기화 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 설정 백업
     */
    public function backup()
    {
        try {
            $settings = $this->loadSettings();
            $filename = 'emoney_settings_backup_' . now()->format('Y-m-d_H-i-s') . '.json';

            $headers = [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            return response()->json($settings, 200, $headers);

        } catch (\Exception $e) {
            \Log::error('Setting backup error', [
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->with('error', '설정 백업 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}