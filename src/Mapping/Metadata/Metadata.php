<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

abstract class Metadata
{
    private readonly int $timestamp;

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

    /**
     * Returns the metadata creation timestamp in seconds.
     *
     * @api
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}
