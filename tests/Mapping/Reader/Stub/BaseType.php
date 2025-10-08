<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(
    field: 'kind',
    map: [
        'a' => TypeA::class,
        'b' => TypeB::class,
    ],
    otherwise: DefaultType::class
)]
abstract class BaseType {}
