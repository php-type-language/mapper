<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context\Path\Entry;

final class ArrayIndexEntry extends Entry
{
    public function __construct(
        public readonly int|string $index,
    ) {
        $key = (string) $this->index;

        parent::__construct($key === '' ? '0' : $key);
    }
}
