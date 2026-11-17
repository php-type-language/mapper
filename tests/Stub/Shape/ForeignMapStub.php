<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Shape;

use TypeLang\Mapper\Mapping\DiscriminatorMap;
use TypeLang\Mapper\Tests\Stub\AddressStub;

/**
 * Map refers to a class outside the hierarchy.
 */
#[DiscriminatorMap(field: 'type', map: ['address' => AddressStub::class])]
abstract class ForeignMapStub {}
