<?php

namespace Limiter\Store;

use Carbon\Carbon;
use Exception;
use Limiter\Contracts\Store;

class FileStore implements Store {

    protected $dir;

    public function __construct(string $dir) {
        $this->dir = $dir;
    }

    public function get(string $key): mixed {
        return $this->getPayload($key)['data'] ?? null;
    }

    public function set(string $key, $value, int $seconds = 0): void {
        $this->ensureCacheDirExists();
        $raw = $this->getPayload($key);
        file_put_contents(
            $this->path($key),
            $this->expiration($raw['time'] === null ? $seconds : $raw['time']) . serialize($value)
        );
    }

    public function increment(string $key, int $amount = 1): int {
        $raw = $this->getPayload($key);
        $value = (int)$raw['data'] + $amount;
        $this->set($key, $value, $raw['time'] ?? 0);
        return $value;
    }

    public function decrement(string $key, int $amount = 1): int {
        return $this->increment($key);
    }

    public function del(string $key): void {
        if (file_exists($file = $this->path($key))) {
            unlink($file);
        }
    }

    private function path(string $key): string {
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
        return join(DIRECTORY_SEPARATOR, [trim($this->dir, DIRECTORY_SEPARATOR), sha1($key)]);
    }

    protected function putCache(string $key, array $cache) {
        file_put_contents($this->path($key), serialize($cache));
    }

    protected function getPayload(string $key): array|null {
        $file = $this->path($key);
        if (!file_exists($file)) {
            return ['data' => null, 'time' => null];
        }
        $contents = file_get_contents($file);
        $expiresAt = substr($contents, 0, 10);
        $now = Carbon::now()->getTimestamp();
        if ($now >= $expiresAt) {
            $this->del($key);
            return ['data' => null, 'time' => null];
        }
        try {
            $data = unserialize(substr($contents, 10));
        } catch (Exception) {
            $this->del($key);
            return ['data' => null, 'time' => null];
        }
        return ['data' => $data, 'time' => $expiresAt - $now];
    }

    protected function ensureCacheDirExists() {
        if (!file_exists($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
    }

    protected function expiration(int $seconds) {
        if ($seconds == 0) {
            return 9999999999;
        }
        $time = Carbon::now()->addRealSeconds($seconds)->getTimestamp();
        return $time > 9999999999 ? 9999999999 : $time;
    }
}
