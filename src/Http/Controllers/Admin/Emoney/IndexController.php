<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Emoney;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Emoney\Models\UserEmoney;

/**
 * 관리자 - 이머니 목록 컨트롤러
 *
 * [메소드 호출 관계 트리]
 * IndexController
 * ├── __construct()
 * │   └── loadConfig() - JSON 설정 파일 로드
 * ├── index(Request $request)
 * │   └── __invoke($request) - 실제 처리 메소드 호출
 * └── __invoke(Request $request)
 *     ├── UserEmoney::with('user') - 사용자 관계 포함 쿼리
 *     ├── 필터링 적용
 *     │   ├── 검색 필터: email, name, user_id
 *     │   └── 상태 필터: status
 *     ├── $query->orderBy($sortBy, $sortOrder) - 정렬 적용
 *     ├── $query->paginate($perPage) - 페이지네이션
 *     ├── 통계 계산
 *     │   ├── UserEmoney::sum('balance') - 총 잔액
 *     │   ├── UserEmoney::sum('point') - 총 포인트
 *     │   └── UserEmoney::count() - 지갑 개수
 *     └── view($this->config['view'], $data) - 뷰 렌더링
 *
 * [컨트롤러 역할]
 * - 관리자용 이머니 지갑 목록 페이지
 * - JSON 설정 파일 기반 동적 구성
 * - 사용자 정보와 함께 이머니 지갑 정보 표시
 * - 검색, 필터링, 정렬, 페이지네이션 기능
 * - 이머니 전체 통계 정보 제공
 *
 * [설정 기반 구성]
 * - Emoney.json 파일에서 뷰, 제목, 페이지네이션 등 설정 로드
 * - 동적 설정으로 유연한 관리 인터페이스 제공
 *
 * [라우트 연결]
 * Route: GET /admin/auth/emoney
 * Name: admin.auth.emoney.index
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
            'view' => $indexConfig['view'] ?? 'jiny-emoney::admin.emoney.index',
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
    public function index(Request $request)
    {
        return $this->__invoke($request);
    }

    /**
     * 이머니 목록 표시 (__invoke 메서드)
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