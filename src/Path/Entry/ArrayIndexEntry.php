<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Path\Entry;

final class ArrayIndexEntry extends Entry
{
    /**
     * @param array-key $index
     */
    public function __construct(
        public readonly int|string $index,
    ) {
        $key = (string) $this->index;
        $key = $key === '' ? '0' : $key;

        parent::__construct($key);
    }
}
