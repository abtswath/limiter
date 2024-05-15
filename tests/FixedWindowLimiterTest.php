<?php

namespace Limiter\Tests\Store;

use Carbon\Carbon;
use Limiter\FixedWindowLimiter;
use Limiter\Store\MemoryStore;
use PHPUnit\Framework\TestCase;

class FixedWindowLimiterTest extends TestCase {

    public function testAttemptsReturnTrueIfNotOverLimit() {
        Carbon::setTestNow(Carbon::now());
        $cache = new MemoryStore();
        $limit = 2;
        $windowSize = 10;
        $limiter = new FixedWindowLimiter($cache, $limit, $windowSize);
        $this->assertTrue($limiter->attempt('key'));
    }

    public function testAttemptsReturnFalseIfOverLimit() {
        Carbon::setTestNow(Carbon::now());
        $cache = new MemoryStore();
        $limit = 2;
        $windowSize = 10;
        $cache->increment('key', $windowSize, $limit);
        $limiter = new FixedWindowLimiter($cache, $limit, $windowSize);
        $this->assertFalse($limiter->attempt('key'));
    }

    public function testAttemptReturnTrueAtNextWindow() {
        Carbon::setTestNow(Carbon::now());
        $cache = new MemoryStore();
        $limit = 2;
        $windowSize = 10;
        $cache->set('key', $limit, $windowSize);
        $limiter = new FixedWindowLimiter($cache, $limit, $windowSize);
        Carbon::setTestNow(Carbon::now()->addSeconds($windowSize));
        $this->assertTrue($limiter->attempt('key'));
    }
}
