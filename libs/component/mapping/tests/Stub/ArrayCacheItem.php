<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use Psr\Cache\CacheItemInterface;

/**
 * Item of the {@see ArrayCachePool}, expiration is ignored.
 */
final class ArrayCacheItem implements CacheItemInterface
{
    public function __construct(
        private readonly string $key,
        private mixed $value = null,
        private bool $isHit = false,
    ) {}

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->isHit;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        $this->isHit = true;

        return $this;
    }

    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        return $this;
    }

    public function expiresAfter(\DateInterval|int|null $time): static
    {
        return $this;
    }
}
