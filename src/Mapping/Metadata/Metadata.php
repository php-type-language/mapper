<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

abstract class Metadata
{
    /**
     * Gets the metadata creation timestamp in seconds.
     */
    public readonly int $timestamp;

    public function __construct(?int $createdAt = null)
    {
        $this->timestamp = $createdAt ?? $this->getCurrentTimestamp();
    }

    private function now(): \DateTimeInterface
    {
        return new \DateTimeImmutable();
    }

    private function getCurrentTimestamp(): int
    {
        $date = $this->now();

        return $date->getTimestamp();
    }
}
