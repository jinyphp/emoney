<?php

namespace Jiny\Auth\Emoney\Http\Controllers\Emoney\Bank;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Jiny\Auth\Emoney\Models\AuthBank;

/**
 * 관리자 - 사용자 은행 계좌 생성 폼 컨트롤러
 */
class CreateController extends Controller
{
    /**
     * 은행 계좌 생성 폼 표시
     */
    public function __invoke(Request $request)
    {
        // 샤딩된 사용자 검색을 위한 필터 옵션
        $filters = [
            'uuid' => $request->get('uuid'),
            'shard' => $request->get('shard'),
            'email' => $request->get('email'),
            'user_id' => $request->get('user_id'),
        ];

        // 사용자 검색 결과 (샤딩된 테이블에서 검색)
        $users = collect();
        if ($request->filled(['uuid', 'shard']) || $request->filled('email') || $request->filled('user_id')) {
            // 검색할 테이블 목록 결정
            $tables = [];
            if ($request->filled('shard')) {
                // 특정 샤드가 지정된 경우
                $shard = $request->get('shard');
                if ($shard == 0) {
                    $tables = ['users'];
                } else {
                    $tables = ["users_" . str_pad($shard, 3, '0', STR_PAD_LEFT)];
                }
            } else {
                // 샤드가 지정되지 않은 경우 모든 테이블 검색
                $tables = ['users', 'users_001', 'users_002'];
            }

            foreach ($tables as $table) {
                // 테이블 존재 여부 확인
                try {
                    $query = DB::table($table);

                    if ($request->filled('uuid')) {
                        $query->where('uuid', $request->get('uuid'));
                    }

                    if ($request->filled('email')) {
                        $query->where('email', 'like', '%' . $request->get('email') . '%');
                    }

                    if ($request->filled('user_id')) {
                        $query->where('id', $request->get('user_id'));
                    }

                    $results = $query->limit(50)->get();

                    // 테이블명 정보 및 샤드 번호 추가
                    foreach ($results as $result) {
                        $result->table_name = $table;

                        // 테이블명에서 샤드 번호 추출
                        if ($table === 'users') {
                            $result->calculated_shard = 0;
                        } else {
                            // users_001, users_002 등에서 숫자 부분 추출
                            preg_match('/users_(\d+)/', $table, $matches);
                            $result->calculated_shard = isset($matches[1]) ? (int)$matches[1] : 0;
                        }

                        // 실제 shard_id가 없으면 계산된 값 사용
                        if (empty($result->shard_id)) {
                            $result->shard_id = $result->calculated_shard;
                        }
                    }

                    $users = $users->merge($results);
                } catch (\Exception $e) {
                    // 테이블이 존재하지 않는 경우 무시
                    continue;
                }
            }

            // 최대 50개로 제한
            $users = $users->take(50);
        }

        // 은행 목록 (auth_banks 테이블에서 활성화된 은행들 조회)
        $banks = AuthBank::getSelectOptions();

        // 통화 목록
        $currencies = [
            'KRW' => '원화 (KRW)',
            'USD' => '달러 (USD)',
            'EUR' => '유로 (EUR)',
            'JPY' => '엔화 (JPY)',
            'CNY' => '위안화 (CNY)',
        ];

        return view('jiny-emoney::emoney.bank.create', [
            'users' => $users,
            'filters' => $filters,
            'banks' => $banks,
            'currencies' => $currencies,
        ]);
    }
}