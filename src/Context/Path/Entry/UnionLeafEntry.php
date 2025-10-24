<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context\Path\Entry;

final class UnionLeafEntry extends Entry
{
    /**
     * @param int<0, max> $index
     */
    public function __construct(
        public readonly int $index,
    ) {
        parent::__construct((string) $this->index);
    }
}
