<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\DiscriminatorMap;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

/**
 * Class of a discriminator map with a private property read in a subclass
 * outside the map.
 */
#[DiscriminatorMap(field: 'type', map: ['tagged' => TaggedSecretStub::class])]
class TaggedSecretStub
{
    public string $name = 'n';

    #[MapGetter, MapSetter]
    private string $secret = 's';
}
