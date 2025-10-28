<?php

namespace Jiny\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 관리자 - 회원 검색 컨트롤러
 */
class SearchMemberController extends Controller
{
    /**
     * 이메일로 회원 검색
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->input('email');

        try {
            // 사용자 검색
            $user = DB::table('users')
                ->where('email', $email)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '해당 이메일의 사용자를 찾을 수 없습니다.'
                ]);
            }

            // 포인트 정보 조회 또는 생성
            $pointInfo = DB::table('user_point')
                ->where('user_id', $user->id)
                ->first();

            if (!$pointInfo) {
                // 포인트 계정이 없는 경우 생성
                DB::table('user_point')->insert([
                    'user_id' => $user->id,
                    'user_uuid' => $user->uuid ?? '',
                    'balance' => 0,
                    'total_earned' => 0,
                    'total_used' => 0,
                    'total_expired' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $pointInfo = (object) [
                    'user_id' => $user->id,
                    'user_uuid' => $user->uuid ?? '',
                    'balance' => 0,
                    'total_earned' => 0,
                    'total_used' => 0,
                    'total_expired' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            return response()->json([
                'success' => true,
                'member' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid ?? '',
                    'name' => $user->name ?? '',
                    'email' => $user->email,
                    'created_at' => $user->created_at
                ],
                'point_info' => [
                    'balance' => $pointInfo->balance ?? 0,
                    'total_earned' => $pointInfo->total_earned ?? 0,
                    'total_used' => $pointInfo->total_used ?? 0,
                    'total_expired' => $pointInfo->total_expired ?? 0
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Member search error', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '회원 검색 중 오류가 발생했습니다.'
            ], 500);
        }
    }
}