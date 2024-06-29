<?php

declare(strict_types=1);

namespace Serafim\Mapper\Meta;

abstract class Metadata
{
    private readonly int $createdAt;

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        private readonly string $name,
        ?int $createdAt = null,
    ) {
        $this->createdAt = $createdAt ?? \time();
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
    public function getCreatedAt(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())
            ->setTimestamp($this->createdAt);
    }
}
