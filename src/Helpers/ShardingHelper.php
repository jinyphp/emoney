<?php

namespace Jiny\Emoney\Helpers;

use Illuminate\Support\Facades\DB;

class ShardingHelper
{
    /**
     * 설정 파일에서 샤딩 개수를 가져옵니다.
     *
     * @return int
     */
    public static function getShardCount(): int
    {
        $settingPath = base_path('vendor/jiny/auth/config/setting.json');

        if (!file_exists($settingPath)) {
            return 16; // 기본값
        }

        $settings = json_decode(file_get_contents($settingPath), true);

        return $settings['sharding']['shard_count'] ?? 16;
    }

    /**
     * UUID를 기반으로 샤딩된 사용자 정보를 가져옵니다.
     *
     * @param string $userUuid
     * @return object|null
     */
    public static function getUserByUuid(string $userUuid): ?object
    {
        // 먼저 기본 users 테이블에서 검색
        try {
            $user = DB::table('users')
                ->where('uuid', $userUuid)
                ->first();

            if ($user) {
                return $user;
            }
        } catch (\Exception $e) {
            // 기본 users 테이블이 존재하지 않는 경우 무시
        }

        $shardCount = self::getShardCount();

        // 설정된 샤딩 개수만큼 테이블을 순차적으로 확인
        for ($i = 1; $i <= $shardCount; $i++) {
            $tableName = 'users_' . str_pad($i, 3, '0', STR_PAD_LEFT);

            try {
                $user = DB::table($tableName)
                    ->where('uuid', $userUuid)
                    ->first();

                if ($user) {
                    return $user;
                }
            } catch (\Exception $e) {
                // 테이블이 존재하지 않는 경우 다음 테이블로 넘어감
                continue;
            }
        }

        return null;
    }

    /**
     * 여러 UUID들의 사용자 정보를 가져옵니다.
     *
     * @param array $userUuids
     * @return array
     */
    public static function getUsersByUuids(array $userUuids): array
    {
        $users = [];
        $shardCount = self::getShardCount();

        // 먼저 기본 users 테이블에서 검색
        try {
            $foundUsers = DB::table('users')
                ->whereIn('uuid', $userUuids)
                ->get();

            foreach ($foundUsers as $user) {
                $users[$user->uuid] = $user;
            }
        } catch (\Exception $e) {
            // 기본 users 테이블이 존재하지 않는 경우 무시
        }

        // 설정된 샤딩 개수만큼 테이블을 순차적으로 확인
        for ($i = 1; $i <= $shardCount; $i++) {
            $tableName = 'users_' . str_pad($i, 3, '0', STR_PAD_LEFT);

            try {
                $foundUsers = DB::table($tableName)
                    ->whereIn('uuid', $userUuids)
                    ->get();

                foreach ($foundUsers as $user) {
                    $users[$user->uuid] = $user;
                }
            } catch (\Exception $e) {
                // 테이블이 존재하지 않는 경우 다음 테이블로 넘어감
                continue;
            }
        }

        return $users;
    }

    /**
     * UUID를 기반으로 해시값을 계산하여 샤드 번호를 반환합니다.
     *
     * @param string $userUuid
     * @return int
     */
    public static function getShardNumber(string $userUuid): int
    {
        $shardCount = self::getShardCount();

        // UUID의 해시값을 기반으로 샤드 번호 계산
        return (crc32($userUuid) % $shardCount) + 1;
    }

    /**
     * 특정 샤드의 테이블명을 반환합니다.
     *
     * @param int $shardNumber
     * @return string
     */
    public static function getShardTableName(int $shardNumber): string
    {
        return 'users_' . str_pad($shardNumber, 3, '0', STR_PAD_LEFT);
    }
}