<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;

final class CountingGetterStub
{
    /**
     * @var list<string>
     */
    public array $items = [];

    /**
     * @var list<string>
     */
    public array $tags = [];

    #[MapGetter('items')]
    public function countItems(): int
    {
        return \count($this->items);
    }

    #[MapGetter('tags')]
    public function getTags(): array
    {
        return $this->tags;
    }
}
