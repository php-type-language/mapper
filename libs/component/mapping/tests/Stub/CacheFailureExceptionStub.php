<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use Psr\Cache\CacheException as Psr6CacheException;
use Psr\SimpleCache\CacheException as Psr16CacheException;

/**
 * Failure of the storage of PSR-6 and PSR-16 caches.
 */
final class CacheFailureExceptionStub extends \RuntimeException implements
    Psr6CacheException,
    Psr16CacheException {}
