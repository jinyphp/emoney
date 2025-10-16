<?php

namespace Jiny\AuthEmoney\Http\Controllers\Admin\Emoney;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\AuthEmoney\Models\UserEmoneyWithdraw;

/**
 * 관리자 - 이머니 출금 목록 컨트롤러
 */
class WithdrawalsController extends Controller
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

        $withdrawalsConfig = $jsonConfig['withdrawals'] ?? [];

        $this->config = [
            'view' => $withdrawalsConfig['view'] ?? 'jiny-auth-emoney::admin.emoney.withdrawals',
            'title' => $withdrawalsConfig['title'] ?? '출금 내역',
            'subtitle' => $withdrawalsConfig['subtitle'] ?? '이머니 출금 목록',
            'per_page' => 20,
            'sort_column' => 'created_at',
            'sort_order' => 'desc',
        ];
    }

    /**
     * 이머니 출금 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserEmoneyWithdraw::with(['user', 'wallet']);

        // 검색 필터
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('trans', 'like', "%{$search}%")
                  ->orWhere('account', 'like', "%{$search}%")
                  ->orWhere('account_name', 'like', "%{$search}%");
            });
        }

        // 상태 필터
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 방법 필터
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // 정렬
        $sortBy = $request->get('sort_by', $this->config['sort_column']);
        $sortOrder = $request->get('sort_order', $this->config['sort_order']);
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $withdrawals = $query->paginate($this->config['per_page'])->withQueryString();

        // 통계
        $stats = [
            'pending' => UserEmoneyWithdraw::where('status', 'pending')->count(),
            'pending_amount' => UserEmoneyWithdraw::where('status', 'pending')->sum('amount'),
        ];

        return view($this->config['view'], compact('withdrawals', 'stats'));
    }
}