<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context\Path;

abstract class Entry implements EntryInterface
{
    /**
     * @param non-empty-string $value
     */
    public function __construct(
        public readonly string $value,
    ) {}

    public function match(mixed $value): bool
    {
        return $this->value === $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
