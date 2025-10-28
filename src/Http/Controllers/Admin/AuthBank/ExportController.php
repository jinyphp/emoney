<?php

namespace Jiny\Emoney\Http\Controllers\Admin\AuthBank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Emoney\Models\AuthBank;
use Symfony\Component\HttpFoundation\Response;

/**
 * 관리자 - 은행 데이터 내보내기 컨트롤러
 *
 * [메소드 호출 관계 트리]
 * ExportController
 * ├── __invoke(Request $request)
 * │   ├── AuthBank::query() - 쿼리 빌더 생성
 * │   ├── $query->search($search) - 검색 필터 적용 (IndexController와 동일)
 * │   ├── $query->byCountry($country) - 국가별 필터 적용
 * │   ├── $query->where('enable', boolean) - 상태 필터 적용
 * │   ├── $query->ordered() 또는 $query->orderBy($sortBy, $sortOrder) - 정렬 적용
 * │   ├── $query->get() - 모든 데이터 조회 (페이지네이션 없이)
 * │   └── 형식별 내보내기 메소드 호출
 * │       ├── exportToCsv($banks) - CSV 형식 내보내기
 * │       ├── exportToExcel($banks) - Excel 형식 내보내기
 * │       └── exportToJson($banks) - JSON 형식 내보내기
 * ├── exportToCsv($banks)
 * │   ├── fopen('php://output', 'w') - 출력 스트림 열기
 * │   ├── fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)) - UTF-8 BOM 추가
 * │   ├── fputcsv($file, $headers) - CSV 헤더 작성
 * │   ├── foreach 루프로 각 레코드를 fputcsv($file, $data) - 데이터 작성
 * │   ├── fclose($file) - 파일 스트림 닫기
 * │   └── response()->stream($callback, 200, $headers) - 스트리밍 응답
 * ├── exportToExcel($banks)
 * │   └── exportToCsv()와 동일한 로직이지만 탭 구분자 사용 및 다른 MIME 타입
 * └── exportToJson($banks)
 *     ├── $banks->map() - 데이터 변환
 *     └── response()->json($data) - JSON 응답
 *
 * [컨트롤러 역할]
 * - 은행 목록 데이터를 다양한 형식(CSV, Excel, JSON)으로 내보내기
 * - IndexController와 동일한 필터링 로직 적용하여 필터된 데이터만 내보내기
 * - 한글 데이터의 Excel 호환성을 위한 UTF-8 BOM 처리
 * - 스트리밍 방식으로 대용량 데이터 처리 최적화
 *
 * [지원 형식]
 * - CSV: 쉼표로 구분된 값, UTF-8 BOM 포함, Excel 호환
 * - Excel: 탭으로 구분된 값, .xlsx MIME 타입, Excel 최적화
 * - JSON: 구조화된 JSON 데이터, API 호환 형식
 *
 * [특별 기능]
 * - 파일명에 타임스탬프 자동 추가
 * - UTF-8 BOM으로 Excel 한글 깨짐 방지
 * - 스트리밍 응답으로 메모리 효율성 확보
 * - 국가코드를 국가명으로 변환하여 표시
 *
 * [라우트 연결]
 * Route: GET /admin/auth/bank/export
 * Name: admin.auth.bank.export
 * Parameters: ?format=csv|excel|json&[IndexController의 모든 필터 파라미터]
 *
 * [관련 컨트롤러]
 * - IndexController: 동일한 필터링 로직 공유
 *
 * [보안 고려사항]
 * - 관리자 권한 확인 필요 (미들웨어에서 처리)
 * - 대용량 데이터 내보내기 시 서버 리소스 고려
 * - 민감한 정보 포함 시 접근 권한 제어 필요
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