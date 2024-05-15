<?php

namespace Limiter\Store;

use Carbon\Carbon;
use Limiter\Contracts\Store;

class MemoryStore implements Store {

    protected array $storage = [];

    public function get(string $key): mixed {
        if (!isset($this->storage[$key])) {
            return null;
        }
        $item = $this->storage[$key];
        $expiresAt = $item['expires_at'] ?? 0;
        if ($expiresAt !== 0 && Carbon::now()->getTimestamp() >= $expiresAt) {
            return null;
        }
        return $item['data'];
    }

    public function set(string $key, $value, int $seconds = 0): void {
        $this->storage[$key] = [
            'expires_at' => $this->calculateExpiration($seconds),
            'data' => $value
        ];
    }

    public function increment(string $key, int $amount = 1): int {
        if (!is_null($value = $this->get($key))) {
            $value += $amount;
            $this->storage[$key]['data'] = $value;
            return $value;
        }
        $this->set($key, $amount, 0);
        return $amount;
    }

    public function decrement(string $key, int $amount = 1): int {
        return $this->increment($key, $amount * -1);
    }

    public function del(string $key): void {
        unset($this->storage[$key]);
    }

    protected function expired(string $key): bool {
        return $this->storage[$key]
            && $this->storage[$key]['expires_at'] !== 0
            && Carbon::now()->getTimestamp() >= $this->storage[$key]['expires_at'];
    }

    protected function calculateExpiration(int $seconds) {
        return $seconds > 0 ? Carbon::now()->getTimestamp() + $seconds : 0;
    }
}
