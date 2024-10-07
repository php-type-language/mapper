<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

abstract class Metadata
{
    private readonly int $timestamp;

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        private readonly string $name,
        ?int $createdAt = null,
    ) {
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
     * Returns entry name.
     *
     * @api
     *
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
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
