<?php

namespace Jiny\Emoney\Http\Controllers\Emoney\Withdraw;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\Traits\JWTAuthTrait;

/**
 * 사용자 - 이머니 출금 페이지 컨트롤러
 *
 * [메소드 호출 관계 트리]
 * IndexController
 * └── __invoke(Request $request)
 *     ├── getAuthenticatedUser($request) - JWT 다중 인증 방식으로 사용자 확인
 *     ├── DB::table('user_emoney')->where('user_uuid', $userUuid)->first() - 사용자 이머니 정보 조회
 *     ├── DB::table('user_emoney_bank') - 사용자 등록 은행계좌 조회
 *     │   ├── ->where('user_id', $userUuid) - 해당 사용자의 계좌만
 *     │   ├── ->where('enable', '1') - 활성화된 계좌만
 *     │   ├── ->orderBy('default', 'desc') - 기본 계좌 우선
 *     │   └── ->orderBy('created_at', 'desc') - 최신 등록순
 *     ├── DB::table('user_emoney_withdrawals') - 최근 출금 내역 조회 (5개)
 *     │   ├── ->where('user_uuid', $userUuid) - 해당 사용자만
 *     │   ├── ->orderBy('created_at', 'desc') - 최신순
 *     │   └── ->limit(5) - 최근 5개
 *     └── view('jiny-emoney::home.withdraw.index', $data) - 출금 페이지 뷰 렌더링
 *
 * [컨트롤러 역할]
 * - 사용자 이머니 출금 페이지 표시
 * - 사용자 인증 및 이머니 잔액 확인
 * - 등록된 은행계좌 목록 제공
 * - 최근 출금 내역 표시 (5개)
 * - 출금 신청 폼 제공
 *
 * [인증 처리]
 * - JWT 토큰 및 세션 기반 다중 인증
 * - 미인증 사용자는 로그인 페이지로 리다이렉트
 * - UUID 기반 사용자 식별
 *
 * [데이터 매핑]
 * - 은행계좌 데이터 뷰 호환성을 위한 필드명 매핑
 * - is_default, bank_name, account_number, account_holder
 *
 * [예외 처리]
 * - 테이블 부재 시 경고 로그 기록
 * - 오류 발생 시 빈 컬렉션으로 대체
 *
 * [라우트 연결]
 * Route: GET /emoney/withdraw
 * Name: home.emoney.withdraw.index
 *
 * [관련 컨트롤러]
 * - StoreController: 출금 신청 처리
 * - HistoryController: 출금 내역 조회
 */
class IndexController extends Controller
{
    use JWTAuthTrait;

    /**
     * 사용자 이머니 출금 페이지
     */
    public function __invoke(Request $request)
    {
        // JWT 토큰을 포함한 다중 인증 방식으로 사용자 확인
        $user = $this->getAuthenticatedUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $userUuid = $user->uuid ?? '';

        // 사용자 이머니 정보 조회
        $emoney = null;
        $bankAccounts = collect();

        if ($userUuid) {
            try {
                $emoney = DB::table('user_emoney')->where('user_uuid', $userUuid)->first();
                $rawBankAccounts = DB::table('user_emoney_bank')
                    ->where('user_id', $userUuid)
                    ->where('enable', '1')
                    ->orderBy('`default`', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                // 뷰에서 사용하는 프로퍼티명으로 매핑
                $bankAccounts = $rawBankAccounts->map(function ($account) {
                    $account->is_default = $account->default == '1';
                    $account->bank_name = $account->bank;
                    $account->account_number = $account->account;
                    $account->account_holder = $account->owner;
                    return $account;
                });

                // 최근 출금 기록 조회 (최근 5개)
                $recentWithdrawals = DB::table('user_emoney_withdrawals')
                    ->where('user_uuid', $userUuid)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
                \Log::warning('Withdraw controller query failed', [
                    'user_uuid' => $userUuid,
                    'error' => $e->getMessage()
                ]);
                $recentWithdrawals = collect();
            }
        } else {
            $recentWithdrawals = collect();
        }

        return view('jiny-emoney::home.withdraw.index', [
            'user' => $user,
            'emoney' => $emoney,
            'bankAccounts' => $bankAccounts,
            'recentWithdrawals' => $recentWithdrawals ?? collect(),
        ]);
    }
}