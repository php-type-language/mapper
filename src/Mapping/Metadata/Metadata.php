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

    private function getCurrentTimestamp(): int
    {
        try {
            $date = new \DateTimeImmutable();

            return $date->getTimestamp();
        } catch (\Throwable) {
            return 0;
        }
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
