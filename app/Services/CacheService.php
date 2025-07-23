<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Clear all cache data.
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        Cache::flush(); // Clear all cache data
    }

    /**
     * Clear cache by a specific key.
     *
     * @param string $key
     * @return void
     */
    public function clearCacheByKey(string $key): void
    {
        Cache::forget($key); // Clear cache for the specific key
    }
}
