<?php

namespace Jiny\Auth\Emoney\Http\Controllers\Admin\Point;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Emoney\Models\UserPoint;

/**
 * 관리자 - 사용자 포인트 편집 페이지 컨트롤러
 */
class EditController extends Controller
{
    /**
     * 포인트 계정 편집 폼 표시
     */
    public function __invoke(Request $request, $id)
    {
        $userPoint = UserPoint::with('user')->findOrFail($id);

        // 최근 포인트 로그 조회 (참고용)
        $recentLogs = $userPoint->logs()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 만료 예정 포인트 조회
        $expiringPoints = $userPoint->getExpiringPoints(30);

        return view('jiny-emoney::admin.point.edit', [
            'userPoint' => $userPoint,
            'recentLogs' => $recentLogs,
            'expiringPoints' => $expiringPoints,
            'request' => $request,
            'adjustmentTypes' => [
                'earn' => '포인트 적립',
                'use' => '포인트 사용',
                'admin_add' => '관리자 추가 지급',
                'admin_subtract' => '관리자 차감',
                'refund' => '포인트 환불',
                'expire' => '포인트 만료',
            ],
        ]);
    }
}