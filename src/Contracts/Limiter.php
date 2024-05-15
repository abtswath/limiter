<?php

namespace Limiter\Contracts;

interface Limiter {
    function attempt(string $key): bool;
}
