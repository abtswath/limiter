<?php

namespace Limiter\Contracts;

interface Store {
    public function get(string $key): mixed;

    public function set(string $key, $value, int $seconds = 0): void;

    public function increment(string $key, int $amount = 1): int;

    public function decrement(string $key, int $amount = 1): int;

    public function del(string $key): void;
}
