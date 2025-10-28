<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Withdrawal;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Facades\Shard;

/**
 * 관리자 - 출금 신청 목록 조회
 */
class IndexController extends Controller
{
    /**
     * 출금 신청 목록 표시
     */
    public function __invoke(Request $request)
    {
        // 사용자 검색을 위한 UUID 수집
        $searchUserUuids = [];
        if ($request->filled('search')) {
            $search = $request->search;

            // 이메일로 사용자 검색 (Shard 파사드 사용)
            if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
                $user = Shard::getUserByEmail($search);
                if ($user && isset($user->uuid)) {
                    $searchUserUuids[] = $user->uuid;
                }
            } else {
                // 이메일이 아닌 경우 기본 테이블에서 이름으로 검색
                try {
                    $users = DB::table('users')
                        ->where('name', 'like', "%{$search}%")
                        ->pluck('uuid');
                    $searchUserUuids = $users->toArray();
                } catch (\Exception $e) {
                    // 테이블 오류시 빈 배열 유지
                    $searchUserUuids = [];
                }
            }
        }

        // 출금 신청 쿼리 구성 (사용자 테이블 조인 제거)
        $query = DB::table('user_emoney_withdrawals')
            ->select(['*']);

        // 검색 필터 (사용자 UUID 또는 예금주명, 참조번호로 검색)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $searchUserUuids) {
                // 예금주명이나 참조번호로 검색
                $q->where('account_holder', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");

                // 검색된 사용자 UUID가 있으면 추가
                if (!empty($searchUserUuids)) {
                    $q->orWhereIn('user_uuid', $searchUserUuids);
                }
            });
        }

        // 상태 필터
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 출금 방법 필터
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // 은행 필터
        if ($request->filled('bank')) {
            $query->where('bank_name', 'like', "%{$request->bank}%");
        }

        // 날짜 범위 필터
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // 금액 범위 필터
        if ($request->filled('amount_from')) {
            $query->where('amount', '>=', $request->amount_from);
        }
        if ($request->filled('amount_to')) {
            $query->where('amount', '<=', $request->amount_to);
        }

        // 정렬
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // 안전한 컬럼명 확인
        $allowedSortColumns = ['created_at', 'amount', 'status', 'checked_at'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 페이지네이션
        $perPage = $request->get('per_page', 20);
        $withdrawals = $query->paginate($perPage)->withQueryString();

        // 출금 신청에서 사용자 UUID 수집 및 사용자 정보 조회
        $userUuids = $withdrawals->pluck('user_uuid')->unique()->filter()->toArray();
        $users = [];

        // 각 UUID에 대해 개별적으로 사용자 정보 조회
        foreach ($userUuids as $uuid) {
            $user = Shard::getUserByUuid($uuid);
            if ($user) {
                $users[$uuid] = $user;
            }
        }

        // 출금 신청에 사용자 정보 추가
        $withdrawals->getCollection()->transform(function ($withdrawal) use ($users) {
            $user = $users[$withdrawal->user_uuid] ?? null;
            $withdrawal->user_name = $user->name ?? 'N/A';
            $withdrawal->user_email = $user->email ?? 'N/A';
            return $withdrawal;
        });

        // 통계 정보
        $statistics = [
            'total_withdrawals' => DB::table('user_emoney_withdrawals')->count(),
            'pending_withdrawals' => DB::table('user_emoney_withdrawals')->where('status', 'pending')->count(),
            'approved_withdrawals' => DB::table('user_emoney_withdrawals')->where('status', 'approved')->count(),
            'rejected_withdrawals' => DB::table('user_emoney_withdrawals')->where('status', 'rejected')->count(),
            'total_amount' => DB::table('user_emoney_withdrawals')->where('status', 'approved')->sum('amount'),
            'total_fees' => DB::table('user_emoney_withdrawals')->where('status', 'approved')->sum('fee'),
            'today_withdrawals' => DB::table('user_emoney_withdrawals')->whereDate('created_at', today())->count(),
            'today_amount' => DB::table('user_emoney_withdrawals')
                ->whereDate('created_at', today())
                ->where('status', 'approved')
                ->sum('amount'),
        ];

        return view('jiny-emoney::admin.withdrawal.index', [
            'withdrawals' => $withdrawals,
            'statistics' => $statistics,
            'request' => $request,
        ]);
    }
}