<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context\Path;

interface EntryInterface extends \Stringable
{
    public function match(mixed $value): bool;

    /**
     * Returns string representation of the path entry.
     *
     * @return non-empty-string
     */
    public function __toString(): string;
}
