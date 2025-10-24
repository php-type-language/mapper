<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context\Path\Entry;

abstract class Entry implements EntryInterface
{
    /**
     * @param non-empty-string $value
     */
    public function __construct(
        public readonly string $value,
    ) {}

    public function __toString(): string
    {
        return $this->value;
    }
}
