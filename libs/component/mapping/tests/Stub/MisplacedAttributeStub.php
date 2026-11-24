<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\NormalizeAsArray;

/**
 * Attributes of the mapper on the places their declarations do not allow.
 */
// @phpstan-ignore-next-line the attribute is misplaced on purpose
#[MapName('user')]
final class MisplacedAttributeStub
{
    // @phpstan-ignore-next-line the attribute is misplaced on purpose
    #[MapType('int')]
    public const LIMIT = 10;

    // @phpstan-ignore-next-line the attribute is misplaced on purpose
    #[NormalizeAsArray]
    public string $name = '';
}
