<?php

namespace Jiny\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Emoney\Models\AuthBank;

/**
 * 관리자 - 은행 목록 컨트롤러
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