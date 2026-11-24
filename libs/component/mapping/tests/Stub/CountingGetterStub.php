<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;

/**
 * Getters typed by their own signature and docblock only.
 */
final class CountingGetterStub
{
    /**
     * @var list<string>
     */
    private array $items = [];

    #[MapGetter('count')]
    public function countItems(): int
    {
        return \count($this->items);
    }

    /**
     * @return list<string>
     */
    #[MapGetter('items')]
    public function getItems(): array
    {
        return $this->items;
    }

    #[MapGetter('raw')]
    public function getRaw(): array
    {
        return $this->items;
    }
}
