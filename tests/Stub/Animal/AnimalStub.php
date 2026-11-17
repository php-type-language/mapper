<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Animal;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

/**
 * Base whose map lists the base before its child.
 */
#[DiscriminatorMap(field: 'kind', map: [
    'animal' => AnimalStub::class,
    'dog' => DogStub::class,
])]
class AnimalStub
{
    public string $name = '';
}
