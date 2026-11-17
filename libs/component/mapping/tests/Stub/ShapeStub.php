<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\DiscriminatorMap;
use TypeLang\Mapper\Mapping\Tests\Stub\Concern\OwnerTrait as Fallback;

#[DiscriminatorMap(field: 'type', map: ['user' => UserStub::class, 'status' => 'StatusStub'], otherwise: 'Fallback')]
abstract class ShapeStub {}
