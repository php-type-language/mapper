<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Path\Entry;

final class ArrayIndexEntry extends Entry
{
    public function __construct(
        public readonly mixed $index,
    ) {
        if (\is_scalar($this->index)) {
            $key = (string) $this->index;
        } else {
            $key = \get_debug_type($this->index);
        }

        $key = $key === '' ? '0' : $key;

        parent::__construct($key);
    }
}
