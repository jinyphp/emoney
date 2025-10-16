<?php

namespace Jiny\Auth\Emoney\Services;

use Illuminate\Support\Facades\DB;
use App\Models\User;

/**
 * 포인트 관리 서비스
 */
class PointService
{
    /**
     * 포인트 적립
     *
     * @param int $userId 사용자 ID
     * @param float $amount 적립 포인트
     * @param string $reason 적립 사유
     * @param string|null $referenceType 참조 타입
     * @param int|null $referenceId 참조 ID
     * @return array
     */
    public function earnPoints($userId, $amount, $reason, $referenceType = null, $referenceId = null)
    {
        return DB::transaction(function () use ($userId, $amount, $reason, $referenceType, $referenceId) {
            // 사용자 포인트 조회 또는 생성
            $userPoint = DB::table('user_point')->where('user_id', $userId)->first();

            if (!$userPoint) {
                DB::table('user_point')->insert([
                    'user_id' => $userId,
                    'balance' => 0,
                    'total_earned' => 0,
                    'total_used' => 0,
                    'total_expired' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $userPoint = DB::table('user_point')->where('user_id', $userId)->first();
            }

            $balanceBefore = $userPoint->balance;
            $balanceAfter = $balanceBefore + $amount;

            // 포인트 잔액 업데이트
            DB::table('user_point')->where('user_id', $userId)->update([
                'balance' => $balanceAfter,
                'total_earned' => DB::raw("total_earned + {$amount}"),
                'updated_at' => now(),
            ]);

            // 포인트 만료일 계산
            $expiresAt = null;
            if (config('admin.auth.point.expiry.enable', true)) {
                $expiryDays = config('admin.auth.point.expiry.days', 365);
                $expiresAt = now()->addDays($expiryDays);
            }

            // 포인트 로그 기록
            $logId = DB::table('user_point_log')->insertGetId([
                'user_id' => $userId,
                'transaction_type' => 'earn',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 만료 스케줄 등록
            if ($expiresAt) {
                DB::table('user_point_expiry')->insert([
                    'user_id' => $userId,
                    'point_log_id' => $logId,
                    'amount' => $amount,
                    'expires_at' => $expiresAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'earned' => $amount,
                'log_id' => $logId,
            ];
        });
    }

    /**
     * 포인트 사용
     *
     * @param int $userId 사용자 ID
     * @param float $amount 사용 포인트
     * @param string $reason 사용 사유
     * @param string|null $referenceType 참조 타입
     * @param int|null $referenceId 참조 ID
     * @return array
     */
    public function usePoints($userId, $amount, $reason, $referenceType = null, $referenceId = null)
    {
        return DB::transaction(function () use ($userId, $amount, $reason, $referenceType, $referenceId) {
            // 사용자 포인트 조회
            $userPoint = DB::table('user_point')->where('user_id', $userId)->first();

            if (!$userPoint) {
                throw new \Exception('포인트 계정이 없습니다.');
            }

            // 잔액 확인
            if ($userPoint->balance < $amount) {
                throw new \Exception('포인트 잔액이 부족합니다.');
            }

            // 최소 사용 포인트 확인
            $minAmount = config('admin.auth.point.use.min_amount', 100);
            if ($amount < $minAmount) {
                throw new \Exception("최소 {$minAmount}P 이상 사용 가능합니다.");
            }

            $balanceBefore = $userPoint->balance;
            $balanceAfter = $balanceBefore - $amount;

            // 포인트 잔액 업데이트
            DB::table('user_point')->where('user_id', $userId)->update([
                'balance' => $balanceAfter,
                'total_used' => DB::raw("total_used + {$amount}"),
                'updated_at' => now(),
            ]);

            // 포인트 로그 기록
            $logId = DB::table('user_point_log')->insertGetId([
                'user_id' => $userId,
                'transaction_type' => 'use',
                'amount' => -$amount, // 음수로 저장
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'used' => $amount,
                'log_id' => $logId,
            ];
        });
    }

    /**
     * 포인트 환불
     *
     * @param int $userId 사용자 ID
     * @param float $amount 환불 포인트
     * @param string $reason 환불 사유
     * @param int|null $originalLogId 원래 거래 로그 ID
     * @return array
     */
    public function refundPoints($userId, $amount, $reason, $originalLogId = null)
    {
        return $this->earnPoints($userId, $amount, $reason, 'PointRefund', $originalLogId);
    }

    /**
     * 포인트 만료 처리
     *
     * @param int $userId 사용자 ID
     * @param int $expiryId 만료 ID
     * @return array
     */
    public function expirePoints($userId, $expiryId)
    {
        return DB::transaction(function () use ($userId, $expiryId) {
            // 만료 정보 조회
            $expiry = DB::table('user_point_expiry')
                ->where('id', $expiryId)
                ->where('user_id', $userId)
                ->where('expired', false)
                ->first();

            if (!$expiry) {
                throw new \Exception('만료 정보를 찾을 수 없습니다.');
            }

            // 사용자 포인트 조회
            $userPoint = DB::table('user_point')->where('user_id', $userId)->first();

            $balanceBefore = $userPoint->balance;
            $balanceAfter = $balanceBefore - $expiry->amount;

            // 포인트 잔액 업데이트
            DB::table('user_point')->where('user_id', $userId)->update([
                'balance' => $balanceAfter,
                'total_expired' => DB::raw("total_expired + {$expiry->amount}"),
                'updated_at' => now(),
            ]);

            // 포인트 로그 기록
            $logId = DB::table('user_point_log')->insertGetId([
                'user_id' => $userId,
                'transaction_type' => 'expire',
                'amount' => -$expiry->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => '포인트 만료',
                'reference_type' => 'PointExpiry',
                'reference_id' => $expiryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 만료 처리 업데이트
            DB::table('user_point_expiry')->where('id', $expiryId)->update([
                'expired' => true,
                'expired_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'expired' => $expiry->amount,
                'log_id' => $logId,
            ];
        });
    }

    /**
     * 사용자 포인트 잔액 조회
     *
     * @param int $userId
     * @return float
     */
    public function getBalance($userId)
    {
        $userPoint = DB::table('user_point')->where('user_id', $userId)->first();

        return $userPoint ? $userPoint->balance : 0;
    }

    /**
     * 포인트 거래 내역 조회
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getTransactionHistory($userId, $limit = 20)
    {
        return DB::table('user_point_log')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 만료 예정 포인트 조회
     *
     * @param int $userId
     * @param int $days 며칠 이내
     * @return \Illuminate\Support\Collection
     */
    public function getExpiringPoints($userId, $days = 30)
    {
        return DB::table('user_point_expiry')
            ->where('user_id', $userId)
            ->where('expired', false)
            ->where('expires_at', '<=', now()->addDays($days))
            ->where('expires_at', '>', now())
            ->orderBy('expires_at', 'asc')
            ->get();
    }

    /**
     * 회원가입 보너스 지급
     *
     * @param int $userId
     * @return array|null
     */
    public function giveSignupBonus($userId)
    {
        if (!config('admin.auth.point.enable', true)) {
            return null;
        }

        $bonusAmount = config('admin.auth.point.earn.signup_bonus', 1000);

        if ($bonusAmount <= 0) {
            return null;
        }

        // 이미 지급되었는지 확인
        $alreadyGiven = DB::table('user_point_log')
            ->where('user_id', $userId)
            ->where('reason', '회원가입 보너스')
            ->exists();

        if ($alreadyGiven) {
            return null;
        }

        return $this->earnPoints($userId, $bonusAmount, '회원가입 보너스', 'SignupBonus', $userId);
    }

    /**
     * 일일 로그인 포인트 지급
     *
     * @param int $userId
     * @return array|null
     */
    public function giveDailyLoginBonus($userId)
    {
        if (!config('admin.auth.point.enable', true)) {
            return null;
        }

        $bonusAmount = config('admin.auth.point.earn.daily_login', 10);

        if ($bonusAmount <= 0) {
            return null;
        }

        // 오늘 이미 지급되었는지 확인
        $alreadyGiven = DB::table('user_point_log')
            ->where('user_id', $userId)
            ->where('reason', '일일 로그인')
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadyGiven) {
            return null;
        }

        return $this->earnPoints($userId, $bonusAmount, '일일 로그인', 'DailyLogin', $userId);
    }

    /**
     * 포인트 통계
     *
     * @param int $userId
     * @return array
     */
    public function getStatistics($userId)
    {
        $userPoint = DB::table('user_point')->where('user_id', $userId)->first();

        if (!$userPoint) {
            return [
                'balance' => 0,
                'total_earned' => 0,
                'total_used' => 0,
                'total_expired' => 0,
                'expiring_soon' => 0,
            ];
        }

        // 만료 예정 포인트 (30일 이내)
        $expiringPoints = DB::table('user_point_expiry')
            ->where('user_id', $userId)
            ->where('expired', false)
            ->where('expires_at', '<=', now()->addDays(30))
            ->where('expires_at', '>', now())
            ->sum('amount');

        return [
            'balance' => $userPoint->balance,
            'total_earned' => $userPoint->total_earned,
            'total_used' => $userPoint->total_used,
            'total_expired' => $userPoint->total_expired,
            'expiring_soon' => $expiringPoints,
        ];
    }

    /**
     * 만료 포인트 일괄 처리
     *
     * @return int 처리된 개수
     */
    public function processExpiredPoints()
    {
        $expiredPoints = DB::table('user_point_expiry')
            ->where('expired', false)
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($expiredPoints as $expiry) {
            try {
                $this->expirePoints($expiry->user_id, $expiry->id);
                $count++;
            } catch (\Exception $e) {
                // 에러 로그
                \Log::error("포인트 만료 처리 실패: {$e->getMessage()}", [
                    'user_id' => $expiry->user_id,
                    'expiry_id' => $expiry->id,
                ]);
            }
        }

        return $count;
    }

    /**
     * 만료 예정 포인트 알림
     *
     * @param int $days 며칠 전 알림
     * @return int 알림 발송 개수
     */
    public function notifyExpiringPoints($days = 7)
    {
        $expiringPoints = DB::table('user_point_expiry')
            ->where('expired', false)
            ->where('notified', false)
            ->whereBetween('expires_at', [now(), now()->addDays($days)])
            ->get();

        $count = 0;

        foreach ($expiringPoints as $expiry) {
            // 알림 발송 (Mail, SMS 등)
            // ...

            // 알림 기록 업데이트
            DB::table('user_point_expiry')->where('id', $expiry->id)->update([
                'notified' => true,
                'notified_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * 관리자 포인트 지급/차감
     *
     * @param int $userId
     * @param float $amount 양수: 지급, 음수: 차감
     * @param string $reason
     * @param int $adminId
     * @return array
     */
    public function adminAdjustment($userId, $amount, $reason, $adminId)
    {
        return DB::transaction(function () use ($userId, $amount, $reason, $adminId) {
            $userPoint = DB::table('user_point')->where('user_id', $userId)->first();

            if (!$userPoint) {
                throw new \Exception('포인트 계정이 없습니다.');
            }

            if ($amount < 0 && abs($amount) > $userPoint->balance) {
                throw new \Exception('포인트 잔액이 부족합니다.');
            }

            $balanceBefore = $userPoint->balance;
            $balanceAfter = $balanceBefore + $amount;

            // 포인트 잔액 업데이트
            DB::table('user_point')->where('user_id', $userId)->update([
                'balance' => $balanceAfter,
                'total_earned' => $amount > 0 ? DB::raw("total_earned + {$amount}") : DB::raw('total_earned'),
                'total_used' => $amount < 0 ? DB::raw("total_used + " . abs($amount)) : DB::raw('total_used'),
                'updated_at' => now(),
            ]);

            // 포인트 로그 기록
            $logId = DB::table('user_point_log')->insertGetId([
                'user_id' => $userId,
                'transaction_type' => 'admin',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
                'admin_id' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'adjustment' => $amount,
                'log_id' => $logId,
            ];
        });
    }

    /**
     * 포인트 사용 가능 금액 계산 (주문 금액 기준)
     *
     * @param int $userId
     * @param float $orderAmount 주문 금액
     * @return array
     */
    public function calculateAvailablePoints($userId, $orderAmount)
    {
        $balance = $this->getBalance($userId);

        // 최대 사용 포인트 (주문당)
        $maxAmountPerOrder = config('admin.auth.point.use.max_amount_per_order', 50000);

        // 최대 사용 비율 (주문 금액의 %)
        $maxRatePerOrder = config('admin.auth.point.use.max_rate_per_order', 50);
        $maxByRate = $orderAmount * ($maxRatePerOrder / 100);

        // 최소값 선택
        $maxUsable = min($balance, $maxAmountPerOrder, $maxByRate);

        // 최소 사용 포인트 확인
        $minAmount = config('admin.auth.point.use.min_amount', 100);

        if ($maxUsable < $minAmount) {
            $maxUsable = 0;
        }

        return [
            'balance' => $balance,
            'max_usable' => $maxUsable,
            'max_by_amount' => $maxAmountPerOrder,
            'max_by_rate' => $maxByRate,
            'min_amount' => $minAmount,
        ];
    }
}