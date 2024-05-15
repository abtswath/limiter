<?php

namespace Limiter;

use Carbon\Carbon;
use Limiter\Contracts\Limiter;
use Limiter\Contracts\Store;

class SlidingLogLimiter implements Limiter {

    protected Store $store;

    protected int $limit;

    protected int $windowSize;

    public function __construct(Store $store, int $limit, int $windowSize) {
        $this->store = $store;
        $this->limit = $limit;
        $this->windowSize = $windowSize;
    }

    public function attempt(string $key): bool {
        $logs = $this->getWindowLogs($key);
        if (count($logs) < $this->limit) {
            $logs[] = Carbon::now()->getTimestamp();
            $this->store->set($key, $logs, 0);
            return true;
        }
        return false;
    }

    protected function getWindowLogs(string $key): array {
        $windowStart = Carbon::now()->getTimestamp() - $this->windowSize;
        $data = $this->store->get($key);
        if (!is_array($data)) {
            $data = [];
        }
        return array_filter($data, function ($value) use ($windowStart) {
            return $value > $windowStart;
        }, ARRAY_FILTER_USE_BOTH);
    }
}
