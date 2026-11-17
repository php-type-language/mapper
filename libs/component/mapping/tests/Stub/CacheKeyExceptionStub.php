<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use Psr\Cache\InvalidArgumentException as Psr6InvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException as Psr16InvalidArgumentException;

/**
 * Rejected key of PSR-6 and PSR-16 caches.
 */
final class CacheKeyExceptionStub extends \InvalidArgumentException implements
    Psr6InvalidArgumentException,
    Psr16InvalidArgumentException {}
