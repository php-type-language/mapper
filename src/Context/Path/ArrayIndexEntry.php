<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context\Path;

final class ArrayIndexEntry extends Entry
{
    /**
     * @param int|non-empty-string $index
     */
    public function __construct(
        public readonly int|string $index,
    ) {
        parent::__construct((string) $this->index);
    }

    public function match(mixed $value): bool
    {
        return $this->index === $value;
    }
}
