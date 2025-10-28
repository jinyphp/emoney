<?php

namespace Jiny\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Emoney\Models\AuthBank;

/**
 * 관리자 - 은행 목록 컨트롤러
 *
 * [메소드 호출 관계 트리]
 * IndexController
 * └── __invoke(Request $request)
 *     ├── AuthBank::query() - 쿼리 빌더 생성
 *     ├── $query->search($search) - 검색 필터 적용
 *     ├── $query->byCountry($country) - 국가별 필터 적용
 *     ├── $query->where('enable', boolean) - 상태 필터 적용
 *     ├── $query->ordered() - 정렬 순서 적용
 *     ├── $query->orderBy($sortBy, $sortOrder) - 커스텀 정렬 적용
 *     ├── $query->paginate($perPage) - 페이지네이션 적용
 *     ├── AuthBank::count() - 전체 개수 집계
 *     ├── AuthBank::where('enable', true)->count() - 활성 은행 개수
 *     ├── AuthBank::where('enable', false)->count() - 비활성 은행 개수
 *     ├── AuthBank::distinct()->count('country') - 국가 개수
 *     ├── AuthBank::getCountryStats() - 국가별 통계
 *     ├── AuthBank::select('country')->distinct()->orderBy('country')->pluck('country') - 국가 목록
 *     └── view('jiny-emoney::admin.auth-bank.index', $data) - 뷰 렌더링
 *
 * [컨트롤러 역할]
 * - 관리자용 은행 목록 페이지를 담당
 * - 검색, 필터링, 정렬, 페이지네이션 기능 제공
 * - 은행 통계 정보 및 국가별 분류 제공
 *
 * [라우트 연결]
 * Route: GET /admin/auth/bank
 * Name: admin.auth.bank.index
 *
 * [관련 컨트롤러]
 * - CreateController: 새 은행 생성 폼
 * - ShowController: 은행 상세 보기
 * - EditController: 은행 수정 폼
 * - ExportController: 은행 목록 내보내기
 */
class IndexController extends Controller
{
    /**
     * 은행 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = AuthBank::query();

        // 검색 기능
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->search($search);
        }

        // 국가 필터
        if ($request->filled('country')) {
            $query->byCountry($request->get('country'));
        }

        // 상태 필터
        if ($request->filled('enable')) {
            $enable = $request->get('enable');
            if ($enable === '1') {
                $query->where('enable', true);
            } elseif ($enable === '0') {
                $query->where('enable', false);
            }
        }

        // 정렬
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortOrder = $request->get('sort_order', 'asc');

        if ($sortBy === 'sort_order') {
            $query->ordered();
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // 페이지네이션
        $perPage = $request->get('per_page', 20);
        $banks = $query->paginate($perPage);

        // 통계 정보
        $statistics = [
            'total_banks' => AuthBank::count(),
            'active_banks' => AuthBank::where('enable', true)->count(),
            'inactive_banks' => AuthBank::where('enable', false)->count(),
            'countries' => AuthBank::distinct()->count('country'),
            'by_country' => AuthBank::getCountryStats()
        ];

        // 국가 목록 (필터용)
        $countries = AuthBank::select('country')
                            ->distinct()
                            ->orderBy('country')
                            ->pluck('country')
                            ->mapWithKeys(function ($country) {
                                $names = [
                                    'KR' => '대한민국',
                                    'US' => '미국',
                                    'JP' => '일본',
                                    'CN' => '중국',
                                    'GB' => '영국',
                                ];
                                return [$country => $names[$country] ?? $country];
                            });

        return view('jiny-emoney::admin.auth-bank.index', [
            'banks' => $banks,
            'statistics' => $statistics,
            'countries' => $countries,
            'request' => $request,
        ]);
    }
}