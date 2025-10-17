<?php

namespace Jiny\Auth\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\User;

/**
 * 관리자 - 사용자 포인트 생성 페이지 컨트롤러
 */
class CreateController extends Controller
{
    /**
     * 포인트 생성 폼 표시
     */
    public function __invoke(Request $request)
    {
        // 사용자 목록 조회 (검색 기능 포함)
        $usersQuery = User::query();

        if ($request->filled('user_search')) {
            $search = $request->get('user_search');
            $usersQuery->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        $users = $usersQuery->orderBy('created_at', 'desc')->limit(100)->get();

        return view('jiny-emoney::admin.point.create', [
            'users' => $users,
            'request' => $request,
        ]);
    }
}