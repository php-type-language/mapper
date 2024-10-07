<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Context\Path\Entry;

interface EntryInterface extends \Stringable
{
    public function match(mixed $value): bool;

    /**
     * Returns string representation of the path entry.
     */
    public function __toString(): string;
}
