<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Vehicle;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

/**
 * Root of two nested maps with different fields.
 */
#[DiscriminatorMap(field: 'kind', map: [
    'car' => CarStub::class,
    'truck' => TruckStub::class,
])]
abstract class VehicleStub {}
