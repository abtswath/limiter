<?php

namespace Limiter\Tests;

use Carbon\Carbon;
use Limiter\SlidingLogLimiter;
use Limiter\Store\MemoryStore;
use PHPUnit\Framework\TestCase;

class SlidingLogLimiterTest extends TestCase {

    public function testAttemptReturnTrueIfWindowNotOverLimit() {
        Carbon::setTestNow(Carbon::now());
        $limit = 2;
        $windowSize = 10;
        $store = new MemoryStore();
        $limiter = new SlidingLogLimiter($store, $limit, $windowSize);
        $this->assertTrue($limiter->attempt('key'));
    }

    public function testAttemptReturnFalseIfWindowOverLimit() {
        Carbon::setTestNow(Carbon::now());
        $limit = 2;
        $windowSize = 10;
        $store = new MemoryStore();
        $now = Carbon::now()->getTimestamp();
        $store->set('key', [$now, $now]);
        $limiter = new SlidingLogLimiter($store, $limit, $windowSize);
        $this->assertFalse($limiter->attempt('key'));
    }

    public function testAttemptReturnTrueAtNextWindow() {
        Carbon::setTestNow(Carbon::now());
        $limit = 2;
        $windowSize = 10;
        $store = new MemoryStore();
        $store->set('key', [Carbon::now()->getTimestamp(), Carbon::now()->addSeconds(5)->getTimestamp()]);
        $limiter = new SlidingLogLimiter($store, $limit, $windowSize);
        Carbon::setTestNow(Carbon::now()->addSecond(10));
        $this->assertTrue($limiter->attempt('key'));
    }

    public function testAttemptReturnFalseAfterSlide() {
        Carbon::setTestNow(Carbon::now());
        $limit = 2;
        $windowSize = 10;
        $store = new MemoryStore();
        $store->set('key', [Carbon::now()->getTimestamp(), Carbon::now()->addSeconds(1)->getTimestamp()]);
        $limiter = new SlidingLogLimiter($store, $limit, $windowSize);
        Carbon::setTestNow(Carbon::now()->addSecond(2));
        $limiter->attempt('key');
        $this->assertFalse($limiter->attempt('key'));
    }
}
