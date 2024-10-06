<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

abstract class Metadata
{
    private readonly int $createdAt;

    /**
     * @param non-empty-string $name
     *
     * @throws \Exception
     */
    public function __construct(
        private readonly string $name,
        ?int $createdAt = null,
    ) {
        $this->createdAt = $createdAt ?? $this->getCurrentTimestamp();
    }

    private function getCurrentTimestamp(): int
    {
        $date = new \DateTimeImmutable();

        return $date->getTimestamp();
    }

    /**
     * @api
     *
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @api
     */
    public function getCreatedAtTimestamp(): int
    {
        return $this->createdAt;
    }

    /**
     * @api
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())
            ->setTimestamp($this->getCreatedAtTimestamp());
    }
}
