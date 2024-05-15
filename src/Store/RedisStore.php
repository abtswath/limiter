<?php

namespace Limiter\Store;

use Redis;
use Limiter\Contracts\Store;

class RedisStore implements Store {

    private Redis $connection;

    public function __construct(Redis $connection) {
        $this->connection = $connection;
    }

    public function get(string $key): mixed {
        $value = $this->connection->get($key);
        return !is_null($value) ? $this->unserialize($value) : null;
    }

    public function set(string $key, $value, int $seconds = 0): void {
        $value = $this->serialize($value);
        if ($seconds === 0) {
            $this->connection->set($key, $value);
        } else {
            $this->connection->setex($key, $seconds, $value);
        }
    }

    public function increment(string $key, int $amount = 1): int {
        return $this->connection->incrBy($key, $amount);
    }

    public function decrement(string $key, int $amount = 1): int {
        return $this->connection->decrBy($key, $amount * -1);
    }

    public function del(string $key): void {
        $this->connection->del($key);
    }

    protected function unserialize($value) {
        return is_numeric($value) ? $value : unserialize($value);
    }

    protected function serialize($value) {
        return is_numeric($value) && !in_array($value, [INF, -INF]) && !is_nan($value) ? $value : serialize($value);
    }
}
