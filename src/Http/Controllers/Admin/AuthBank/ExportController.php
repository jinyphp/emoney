<?php

namespace Jiny\Auth\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Emoney\Models\AuthBank;
use Symfony\Component\HttpFoundation\Response;

/**
 * 관리자 - 은행 데이터 내보내기 컨트롤러
 */
class ExportController extends Controller
{
    /**
     * 은행 데이터를 Excel/CSV로 내보내기
     */
    public function __invoke(Request $request)
    {
        // 내보내기 형식 확인 (기본값: csv)
        $format = $request->get('format', 'csv');

        // 쿼리 빌더 생성 (IndexController와 동일한 필터링 로직)
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

        // 모든 데이터 가져오기 (페이지네이션 없이)
        $banks = $query->get();

        // 형식에 따라 처리
        switch ($format) {
            case 'excel':
                return $this->exportToExcel($banks);
            case 'json':
                return $this->exportToJson($banks);
            default:
                return $this->exportToCsv($banks);
        }
    }

    /**
     * CSV 형식으로 내보내기
     */
    private function exportToCsv($banks)
    {
        $filename = 'banks_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($banks) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM 추가 (Excel에서 한글 깨짐 방지)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // 헤더 작성
            fputcsv($file, [
                'ID',
                '은행명',
                '은행코드',
                '국가코드',
                '국가명',
                'SWIFT코드',
                '웹사이트',
                '전화번호',
                '설명',
                '활성상태',
                '정렬순서',
                '등록일',
                '수정일'
            ]);

            // 데이터 작성
            foreach ($banks as $bank) {
                fputcsv($file, [
                    $bank->id,
                    $bank->name,
                    $bank->code ?: '',
                    $bank->country,
                    $bank->country_name,
                    $bank->swift_code ?: '',
                    $bank->website ?: '',
                    $bank->phone ?: '',
                    $bank->description ?: '',
                    $bank->enable ? '활성' : '비활성',
                    $bank->sort_order,
                    $bank->created_at->format('Y-m-d H:i:s'),
                    $bank->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Excel 형식으로 내보내기 (CSV와 동일하지만 확장자만 다름)
     */
    private function exportToExcel($banks)
    {
        $filename = 'banks_' . date('Y-m-d_H-i-s') . '.xlsx';

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        // 간단한 Excel 형식으로 출력 (실제로는 CSV 형식이지만 Excel에서 잘 열림)
        $callback = function() use ($banks) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM 추가
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // 헤더 작성
            fputcsv($file, [
                'ID',
                '은행명',
                '은행코드',
                '국가코드',
                '국가명',
                'SWIFT코드',
                '웹사이트',
                '전화번호',
                '설명',
                '활성상태',
                '정렬순서',
                '등록일',
                '수정일'
            ], "\t"); // 탭으로 구분

            // 데이터 작성
            foreach ($banks as $bank) {
                fputcsv($file, [
                    $bank->id,
                    $bank->name,
                    $bank->code ?: '',
                    $bank->country,
                    $bank->country_name,
                    $bank->swift_code ?: '',
                    $bank->website ?: '',
                    $bank->phone ?: '',
                    $bank->description ?: '',
                    $bank->enable ? '활성' : '비활성',
                    $bank->sort_order,
                    $bank->created_at->format('Y-m-d H:i:s'),
                    $bank->updated_at->format('Y-m-d H:i:s')
                ], "\t");
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * JSON 형식으로 내보내기
     */
    private function exportToJson($banks)
    {
        $filename = 'banks_' . date('Y-m-d_H-i-s') . '.json';

        $data = $banks->map(function ($bank) {
            return [
                'id' => $bank->id,
                'name' => $bank->name,
                'code' => $bank->code,
                'country' => $bank->country,
                'country_name' => $bank->country_name,
                'swift_code' => $bank->swift_code,
                'website' => $bank->website,
                'phone' => $bank->phone,
                'description' => $bank->description,
                'enable' => $bank->enable,
                'sort_order' => $bank->sort_order,
                'created_at' => $bank->created_at->toISOString(),
                'updated_at' => $bank->updated_at->toISOString()
            ];
        });

        return response()->json([
            'success' => true,
            'export_date' => now()->toISOString(),
            'total_records' => $banks->count(),
            'data' => $data
        ], 200, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}