<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    /**
     * Cache key prefix for the model
     */
    protected function getCacheKeyPrefix(): string
    {
        return strtolower(class_basename($this)) . '_';
    }

    /**
     * Get cache key for a specific method and parameters
     */
    protected function getCacheKey(string $method, array $params = []): string
    {
        $key = $this->getCacheKeyPrefix() . $method;
        
        if (!empty($params)) {
            $key .= '_' . md5(serialize($params));
        }
        
        return $key;
    }

    /**
     * Cache a query result
     */
    protected function cacheQuery(string $method, callable $callback, array $params = [], int $ttl = 3600)
    {
        $cacheKey = $this->getCacheKey($method, $params);
        
        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Clear cache for this model
     */
    public function clearModelCache(): void
    {
        $prefix = $this->getCacheKeyPrefix();
        
        // Get the cache store driver
        $driver = Cache::getStore();
        
        // Handle different cache drivers
        if (method_exists($driver, 'getRedis')) {
            // Redis driver - use pattern matching
            try {
                $redis = $driver->getRedis();
                $pattern = $prefix . '*';
                $keys = $redis->keys($pattern);
                
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } catch (\Exception $e) {
                // Fallback to individual key clearing if Redis fails
                $this->clearCacheByKnownKeys($prefix);
            }
        } else {
            // For database, file, and other drivers - clear known cache keys
            $this->clearCacheByKnownKeys($prefix);
        }
    }
    
    /**
     * Clear cache by known keys (fallback method)
     */
    protected function clearCacheByKnownKeys(string $prefix): void
    {
        // Clear common cache keys that this trait creates
        $commonKeys = [
            $prefix . 'all',
            $prefix . 'active',
            $prefix . 'count',
        ];
        
        // Add relation-specific keys if model has ID
        if (isset($this->id)) {
            $relations = $this->getKnownRelations();
            foreach ($relations as $relation) {
                $commonKeys[] = $this->getCacheKey('count_' . $relation, ['id' => $this->id]);
                $commonKeys[] = $this->getCacheKey('relation_' . $relation, ['id' => $this->id]);
            }
        }
        
        // Clear each key individually
        foreach ($commonKeys as $key) {
            Cache::forget($key);
        }
    }
    
    /**
     * Get known relations for cache clearing
     * Override this method in your models to specify relations
     */
    protected function getKnownRelations(): array
    {
        return [];
    }

    /**
     * Clear cache when model is updated
     */
    protected static function bootCacheable()
    {
        static::saved(function ($model) {
            if (method_exists($model, 'clearModelCache')) {
                $model->clearModelCache();
            }
        });

        static::deleted(function ($model) {
            if (method_exists($model, 'clearModelCache')) {
                $model->clearModelCache();
            }
        });
    }

    /**
     * Get cached count for a relationship
     */
    public function getCachedCount(string $relation, int $ttl = 1800): int
    {
        return $this->cacheQuery(
            'count_' . $relation,
            fn() => $this->{$relation}()->count(),
            ['id' => $this->id],
            $ttl
        );
    }

    /**
     * Get cached relationship data
     */
    public function getCachedRelation(string $relation, int $ttl = 1800)
    {
        return $this->cacheQuery(
            'relation_' . $relation,
            fn() => $this->{$relation},
            ['id' => $this->id],
            $ttl
        );
    }
}