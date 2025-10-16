<?php

namespace Jiny\AuthEmoney\Http\Controllers\Admin\Emoney;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\AuthEmoney\Models\UserEmoney;

/**
 * 관리자 - 이머니 목록 컨트롤러
 */
class IndexController extends Controller
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

        $indexConfig = $jsonConfig['index'] ?? [];

        $this->config = [
            'view' => $indexConfig['view'] ?? 'jiny-auth-emoney::admin.emoney.index',
            'title' => $indexConfig['title'] ?? '이머니 관리',
            'subtitle' => $indexConfig['subtitle'] ?? '사용자 전자지갑 목록',
            'per_page' => $indexConfig['pagination']['per_page'] ?? 20,
            'sort_column' => $jsonConfig['table']['sort']['column'] ?? 'created_at',
            'sort_order' => $jsonConfig['table']['sort']['order'] ?? 'desc',
            'filter_search' => $indexConfig['filter']['search'] ?? true,
            'filter_status' => $indexConfig['filter']['status'] ?? true,
            'stats_enabled' => $indexConfig['stats'] ?? [],
        ];
    }

    /**
     * 이머니 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserEmoney::with('user');

        // 검색 필터
        if ($this->config['filter_search'] && $request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('user_id', 'like', "%{$search}%");
            });
        }

        // 상태 필터
        if ($this->config['filter_status'] && $request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 정렬
        $sortBy = $request->get('sort_by', $this->config['sort_column']);
        $sortOrder = $request->get('sort_order', $this->config['sort_order']);
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $wallets = $query->paginate($this->config['per_page'])->withQueryString();

        // 통계
        $stats = [
            'total_balance' => UserEmoney::where('status', 'active')->sum('balance'),
            'total_points' => UserEmoney::where('status', 'active')->sum('point'),
            'active_wallets' => UserEmoney::where('status', 'active')->count(),
            'total_wallets' => UserEmoney::count(),
        ];

        return view($this->config['view'], compact('wallets', 'stats'));
    }
}