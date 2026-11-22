<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\Direction;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapOnly;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub as Owner;

final class AccessorStub
{
    public string $name = '';

    /**
     * @var list<int>
     */
    public array $items = [];

    public mixed $owner = null;

    #[MapOnly(Direction::Normalization)]
    public string $createdAt = '';

    #[MapOnly(Direction::Denormalization)]
    public string $password = '';

    #[MapGetter('name')]
    public function getName(): string
    {
        return \ucfirst($this->name);
    }

    #[MapSetter('name')]
    #[MapType('non-empty-string', strict: true)]
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return list<positive-int>
     */
    #[MapGetter('items')]
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param list<positive-int> $items
     */
    #[MapSetter('items')]
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    #[MapGetter('owner')]
    #[MapType('?Owner')]
    public function getOwner(): mixed
    {
        return $this->owner;
    }
}
