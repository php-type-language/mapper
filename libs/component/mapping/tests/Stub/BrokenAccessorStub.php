<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\MapType;

final class BrokenAccessorStub
{
    public string $name = '';

    public string $title = '';

    #[MapGetter]
    public function getName(): string
    {
        return $this->name;
    }

    #[MapGetter('missing')]
    public function getMissing(): string
    {
        return '';
    }

    #[MapGetter('title')]
    #[MapType('string', strict: true)]
    public function getTitle(): string
    {
        return $this->title;
    }

    #[MapSetter('name')]
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    #[MapSetter('name')]
    public function changeName(string $name): void
    {
        $this->name = $name;
    }
}
