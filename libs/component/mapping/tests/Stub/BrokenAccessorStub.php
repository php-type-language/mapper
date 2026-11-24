<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\SkipWhenNull;

/**
 * Wrong attributes of the members.
 */
final class BrokenAccessorStub
{
    #[MapGetter, MapName('renamed')]
    public string $helped = '';

    #[MapSetter, SkipWhenNull]
    public ?string $written = null;

    #[MapGetter]
    public function getName(): string
    {
        return '';
    }

    #[MapGetter('title')]
    #[MapType('string')]
    public function getTitle(): string
    {
        return '';
    }

    #[SkipWhenNull]
    public function getSkipped(): ?string
    {
        return null;
    }
}
