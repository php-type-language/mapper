<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Vehicle;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(field: 'fuel', map: [
    'petrol' => PetrolCarStub::class,
    'electric' => ElectricCarStub::class,
])]
abstract class CarStub extends VehicleStub {}
