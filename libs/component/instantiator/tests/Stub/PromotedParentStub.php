<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Tests\Stub;

class PromotedParentStub
{
    public function __construct(
        public int $count = 3,
        private \ArrayObject $items = new \ArrayObject(),
    ) {}

    public function getItems(): \ArrayObject
    {
        return $this->items;
    }
}
