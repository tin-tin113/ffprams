<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class GeoMapCache
{
    public const VERSION_KEY = 'geo-map:cache-version';
    public const TTL_SECONDS = 60;

    public static function currentVersion(): int
    {
        $version = Cache::get(self::VERSION_KEY);

        if (!is_numeric($version) || (int) $version < 1) {
            Cache::forever(self::VERSION_KEY, 1);

            return 1;
        }

        return (int) $version;
    }

    public static function bumpVersion(): int
    {
        if (!Cache::has(self::VERSION_KEY)) {
            Cache::forever(self::VERSION_KEY, 1);

            return 1;
        }

        return (int) Cache::increment(self::VERSION_KEY);
    }

    public static function buildDataCacheKey(?int $agencyId, ?int $programNameId, ?string $quadrant = null, ?string $status = null, ?string $sector = null): string
    {
        return sprintf(
            'geo-map:v%s:agency:%s:program:%s:quadrant:%s:status:%s:sector:%s',
            self::currentVersion(),
            $agencyId ?? 'all',
            $programNameId ?? 'all',
            $quadrant ?? 'all',
            $status ?? 'all',
            $sector ?? 'all'
        );
    }

    public static function ttlSeconds(): int
    {
        return self::TTL_SECONDS;
    }
}
