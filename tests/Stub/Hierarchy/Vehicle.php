<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Hierarchy;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(field: 'type', map: ['car' => Car::class, 'sportcar' => SportCar::class, 'truck' => Truck::class])]
abstract class Vehicle {}
