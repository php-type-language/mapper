<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(
    field: 'type',
    map: [
        'cat' => Cat::class,
        'dog' => Dog::class,
    ]
)]
abstract class Animal
{
    public string $name;
}
