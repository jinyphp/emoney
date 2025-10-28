<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Emoney\Models\UserPointExpiry;
use Jiny\Auth\Models\JwtAuth;
use Jiny\Auth\Facades\Shard;

/**
 * 관리자 - 포인트 만료 관리 컨트롤러
 */
class ExpiryController extends Controller
{
    /**
     * 포인트 만료 스케줄 목록 표시
     */
    public function __invoke(Request $request)
    {
        // DB 쿼리 빌더를 사용하여 조인 없이 처리
        $query = DB::table('user_point_expiry');

        // 검색 기능 (샤딩된 사용자 검색 지원)
        if ($request->filled('search')) {
            $search = $request->get('search');

            // 먼저 사용자 검색 (이메일/이름으로)
            $userIds = $this->searchUserIds($search);

            if (!empty($userIds)) {
                $query->where(function($q) use ($search, $userIds) {
                    $q->whereIn('user_id', $userIds)
                      ->orWhere('user_id', 'like', "%{$search}%");
                });
            } else {
                $query->where('user_id', 'like', "%{$search}%");
            }
        }

        // 만료 상태 필터
        if ($request->filled('expired')) {
            $query->where('expired', $request->get('expired') == '1');
        }

        // 알림 상태 필터
        if ($request->filled('notified')) {
            $query->where('notified', $request->get('notified') == '1');
        }

        // 만료일 범위 필터
        if ($request->filled('expires_from')) {
            $query->where('expires_at', '>=', $request->get('expires_from'));
        }
        if ($request->filled('expires_to')) {
            $query->where('expires_at', '<=', $request->get('expires_to') . ' 23:59:59');
        }

        // 만료 임박 필터
        if ($request->filled('expiring_days')) {
            $days = (int)$request->get('expiring_days');
            $query->where('expired', false)
                  ->where('expires_at', '<=', now()->addDays($days))
                  ->where('expires_at', '>', now());
        }

        // 금액 범위 필터
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->get('amount_min'));
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->get('amount_max'));
        }

        // 정렬
        $sortBy = $request->get('sort_by', 'expires_at');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $perPage = $request->get('per_page', 20);
        $expiries = $query->paginate($perPage);

        // 각 만료 데이터에 사용자 정보 추가
        foreach ($expiries as $expiry) {
            $expiry->user_data = $this->getUserData($expiry->user_id, $expiry->user_uuid);
        }

        // 통계 정보
        $statistics = [
            'total_schedules' => UserPointExpiry::count(),
            'pending_expiries' => UserPointExpiry::where('expired', false)->count(),
            'expired_count' => UserPointExpiry::where('expired', true)->count(),
            'total_pending_amount' => UserPointExpiry::where('expired', false)->sum('amount'),
            'total_expired_amount' => UserPointExpiry::where('expired', true)->sum('amount'),
            'expiring_today' => UserPointExpiry::where('expired', false)
                ->whereDate('expires_at', today())
                ->count(),
            'expiring_this_week' => UserPointExpiry::where('expired', false)
                ->whereBetween('expires_at', [now(), now()->addWeek()])
                ->count(),
            'expiring_this_month' => UserPointExpiry::where('expired', false)
                ->whereBetween('expires_at', [now(), now()->addMonth()])
                ->count(),
            'notification_pending' => UserPointExpiry::where('expired', false)
                ->where('notified', false)
                ->where('expires_at', '<=', now()->addDays(7))
                ->count(),
            'monthly_expiry_schedule' => UserPointExpiry::select(
                DB::raw('strftime("%Y-%m", expires_at) as month'),
                DB::raw('count(*) as count'),
                DB::raw('sum(amount) as total_amount')
            )
                ->where('expired', false)
                ->where('expires_at', '>=', now())
                ->groupBy('month')
                ->orderBy('month')
                ->limit(12)
                ->get(),
        ];

        return view('jiny-emoney::admin.point-expiry.index', [
            'expiries' => $expiries,
            'statistics' => $statistics,
            'request' => $request,
        ]);
    }

    /**
     * 포인트 만료 스케줄 상세보기
     */
    public function show($id)
    {
        // 만료 정보 조회
        $expiry = DB::table('user_point_expiry')->where('id', $id)->first();

        if (!$expiry) {
            abort(404, '만료 스케줄을 찾을 수 없습니다.');
        }

        // 사용자 정보 조회
        $user_data = $this->getUserData($expiry->user_id, $expiry->user_uuid);

        // 해당 사용자의 만료 예정 통계
        $user_expiry_stats = DB::table('user_point_expiry')
            ->where('user_id', $expiry->user_id)
            ->where('expired', false)
            ->selectRaw('COUNT(*) as count, SUM(amount) as total_amount')
            ->first();

        // 오늘 전체 만료 예정 건수
        $today_expiry_count = DB::table('user_point_expiry')
            ->where('expired', false)
            ->whereDate('expires_at', today())
            ->count();

        return view('jiny-emoney::admin.point-expiry.show', [
            'expiry' => $expiry,
            'user_data' => $user_data,
            'user_expiry_count' => $user_expiry_stats->count ?? 0,
            'user_expiry_amount' => $user_expiry_stats->total_amount ?? 0,
            'today_expiry_count' => $today_expiry_count,
        ]);
    }

    /**
     * 만료 처리 실행
     */
    public function processExpiry($id)
    {
        try {
            $expiry = UserPointExpiry::find($id);

            if (!$expiry) {
                return response()->json(['success' => false, 'message' => '만료 스케줄을 찾을 수 없습니다.']);
            }

            if ($expiry->expired) {
                return response()->json(['success' => false, 'message' => '이미 만료 처리된 스케줄입니다.']);
            }

            // 만료 처리 실행
            $result = $expiry->processExpiry();

            if ($result) {
                return response()->json(['success' => true, 'message' => '만료 처리가 완료되었습니다.']);
            } else {
                return response()->json(['success' => false, 'message' => '만료 처리에 실패했습니다.']);
            }

        } catch (\Exception $e) {
            \Log::error('Admin Point Expiry processExpiry error', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'message' => '만료 처리 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }
    }

    /**
     * 만료 알림 발송
     */
    public function sendNotification($id)
    {
        try {
            $expiry = UserPointExpiry::find($id);

            if (!$expiry) {
                return response()->json(['success' => false, 'message' => '만료 스케줄을 찾을 수 없습니다.']);
            }

            if ($expiry->expired) {
                return response()->json(['success' => false, 'message' => '이미 만료된 스케줄입니다.']);
            }

            if ($expiry->notified) {
                return response()->json(['success' => false, 'message' => '이미 알림이 발송된 스케줄입니다.']);
            }

            // TODO: 실제 알림 발송 로직 구현 (이메일, SMS 등)
            // 현재는 알림 발송됨으로만 표시
            $expiry->markAsNotified();

            return response()->json(['success' => true, 'message' => '알림이 발송되었습니다.']);

        } catch (\Exception $e) {
            \Log::error('Admin Point Expiry sendNotification error', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'message' => '알림 발송 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }
    }

    /**
     * 만료 스케줄 삭제
     */
    public function destroy($id)
    {
        try {
            $expiry = UserPointExpiry::find($id);

            if (!$expiry) {
                return response()->json(['success' => false, 'message' => '만료 스케줄을 찾을 수 없습니다.']);
            }

            if ($expiry->expired) {
                return response()->json(['success' => false, 'message' => '이미 만료 처리된 스케줄은 삭제할 수 없습니다.']);
            }

            $expiry->delete();

            return response()->json(['success' => true, 'message' => '만료 스케줄이 삭제되었습니다.']);

        } catch (\Exception $e) {
            \Log::error('Admin Point Expiry destroy error', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }
    }

    /**
     * 엑셀 파일 다운로드 (CSV 형태)
     */
    public function export(Request $request)
    {
        try {
            // 동일한 필터링 로직 적용
            $query = DB::table('user_point_expiry');

            // 검색 기능
            if ($request->filled('search')) {
                $search = $request->get('search');
                $userIds = $this->searchUserIds($search);

                if (!empty($userIds)) {
                    $query->where(function($q) use ($search, $userIds) {
                        $q->whereIn('user_id', $userIds)
                          ->orWhere('user_id', 'like', "%{$search}%");
                    });
                } else {
                    $query->where('user_id', 'like', "%{$search}%");
                }
            }

            // 만료 상태 필터
            if ($request->filled('expired')) {
                $query->where('expired', $request->get('expired') == '1');
            }

            // 알림 상태 필터
            if ($request->filled('notified')) {
                $query->where('notified', $request->get('notified') == '1');
            }

            // 만료일 범위 필터
            if ($request->filled('expires_from')) {
                $query->where('expires_at', '>=', $request->get('expires_from'));
            }
            if ($request->filled('expires_to')) {
                $query->where('expires_at', '<=', $request->get('expires_to') . ' 23:59:59');
            }

            // 만료 임박 필터
            if ($request->filled('expiring_days')) {
                $days = (int)$request->get('expiring_days');
                $query->where('expired', false)
                      ->where('expires_at', '<=', now()->addDays($days))
                      ->where('expires_at', '>', now());
            }

            // 정렬
            $sortBy = $request->get('sort_by', 'expires_at');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // 데이터 조회 (페이지네이션 없이 모든 데이터)
            $expiries = $query->get();

            // 각 만료 데이터에 사용자 정보 추가
            foreach ($expiries as $expiry) {
                $expiry->user_data = $this->getUserData($expiry->user_id, $expiry->user_uuid);
            }

            // CSV 헤더 설정
            $filename = '포인트_만료_목록_' . now()->format('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ];

            // CSV 데이터 생성
            $callback = function() use ($expiries) {
                $file = fopen('php://output', 'w');

                // UTF-8 BOM 추가 (엑셀에서 한글 깨짐 방지)
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // CSV 헤더 작성
                fputcsv($file, [
                    'ID',
                    '사용자ID',
                    '사용자명',
                    '이메일',
                    '포인트 금액',
                    '만료일',
                    '만료상태',
                    '알림상태',
                    '생성일시',
                    '만료처리일시',
                    '알림발송일시',
                    '샤드ID',
                    '포인트로그ID'
                ]);

                // 데이터 작성
                foreach ($expiries as $expiry) {
                    $userData = $expiry->user_data;

                    fputcsv($file, [
                        $expiry->id,
                        $expiry->user_id,
                        $userData->name ?? '',
                        $userData->email ?? '',
                        number_format($expiry->amount, 0),
                        \Carbon\Carbon::parse($expiry->expires_at)->format('Y-m-d H:i:s'),
                        $expiry->expired ? '만료완료' : '만료대기',
                        $expiry->notified ? '발송완료' : '미발송',
                        \Carbon\Carbon::parse($expiry->created_at)->format('Y-m-d H:i:s'),
                        $expiry->expired_at ? \Carbon\Carbon::parse($expiry->expired_at)->format('Y-m-d H:i:s') : '',
                        $expiry->notified_at ? \Carbon\Carbon::parse($expiry->notified_at)->format('Y-m-d H:i:s') : '',
                        $expiry->shard_id ?? '',
                        $expiry->point_log_id ?? ''
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            \Log::error('Admin Point Expiry export error', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', '엑셀 다운로드 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 사용자 검색 (이메일/이름으로 user_id 목록 반환)
     *
     * @param string $search
     * @return array
     */
    private function searchUserIds($search)
    {
        $userIds = [];

        try {
            // 메인 users 테이블에서 검색
            $mainUsers = DB::table('users')
                ->where('email', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->pluck('id')
                ->toArray();

            $userIds = array_merge($userIds, $mainUsers);

            // 샤딩된 테이블에서 검색 (users_001, users_002 등)
            $shardTables = ['users_001', 'users_002']; // 실제 환경에 맞게 조정

            foreach ($shardTables as $tableName) {
                try {
                    $shardUsers = DB::table($tableName)
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->pluck('id')
                        ->toArray();

                    $userIds = array_merge($userIds, $shardUsers);
                } catch (\Exception $e) {
                    // 테이블이 없으면 무시하고 계속
                    continue;
                }
            }

        } catch (\Exception $e) {
            \Log::warning('Admin Point Expiry search error', [
                'search' => $search,
                'error' => $e->getMessage()
            ]);
        }

        return array_unique($userIds);
    }

    /**
     * 사용자 데이터 조회
     *
     * @param int $userId
     * @param string $userUuid
     * @return object|null
     */
    private function getUserData($userId, $userUuid = null)
    {
        try {
            // user_id로 먼저 조회
            if ($userId) {
                // 메인 users 테이블에서 조회
                $user = DB::table('users')->where('id', $userId)->first();
                if ($user) {
                    return $user;
                }

                // JwtAuth 모델을 통한 샤딩된 사용자 조회
                $jwtUser = JwtAuth::find($userId);
                if ($jwtUser) {
                    return (object) $jwtUser->toArray();
                }
            }

            // user_uuid로 조회
            if ($userUuid) {
                $jwtUser = JwtAuth::findByUuid($userUuid);
                if ($jwtUser) {
                    return (object) $jwtUser->toArray();
                }
            }

            // 모든 방법이 실패하면 기본 객체 반환
            return (object) [
                'id' => $userId,
                'uuid' => $userUuid,
                'name' => '알 수 없는 사용자',
                'email' => '',
            ];

        } catch (\Exception $e) {
            \Log::warning('Admin Point Expiry getUserData error', [
                'user_id' => $userId,
                'user_uuid' => $userUuid,
                'error' => $e->getMessage()
            ]);

            return (object) [
                'id' => $userId,
                'uuid' => $userUuid,
                'name' => '조회 오류',
                'email' => '',
            ];
        }
    }
}