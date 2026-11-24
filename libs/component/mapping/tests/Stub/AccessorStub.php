<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\SkipWhenEmpty;
use TypeLang\Mapper\Mapping\SkipWhenNull;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub as Owner;

/**
 * Members declared by the attributes of the properties and the methods.
 */
final class AccessorStub
{
    private string $name = '';

    /**
     * @var list<int>
     */
    private array $items = [];

    #[MapGetter('created_at')]
    public string $createdAt = '';

    #[MapSetter('secret', aliases: ['pass'], onUndefinedError: 'Password is required')]
    private string $password = '';

    #[MapGetter, MapSetter('code_in'), SkipWhenNull]
    public ?string $code = null;

    #[MapGetter('name')]
    #[SkipWhenEmpty]
    public function getName(): string
    {
        return \ucfirst($this->name . $this->password);
    }

    #[MapSetter('name', type: 'non-empty-string', strict: true)]
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
    #[MapSetter('items', aliases: 'list')]
    public function setItems(array $items = []): void
    {
        $this->items = $items;
    }

    #[MapGetter('owner', type: '?Owner')]
    public function getOwner(): mixed
    {
        return null;
    }
}
