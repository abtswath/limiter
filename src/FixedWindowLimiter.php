<?php

namespace Limiter;

use Limiter\Contracts\Limiter;
use Limiter\Contracts\Store;

class FixedWindowLimiter implements Limiter {

    protected int $limit;

    protected int $windowSize;

    protected Store $store;

    public function __construct(Store $store, int $limit, int $windowSize) {
        $this->store = $store;
        $this->limit = $limit;
        $this->windowSize = $windowSize;
    }

    public function attempt(string $key): bool {
        $value = $this->store->get($key);
        if (is_null($value)) {
            $this->store->set($key, 0, $this->windowSize);
            $value = 0;
        }
        if ((int) $value >= $this->limit) {
            return false;
        }
        $this->store->increment($key);
        return true;
    }
}
