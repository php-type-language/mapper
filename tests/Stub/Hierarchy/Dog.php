<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Hierarchy;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(field: 'breed', map: ['lab' => Lab::class, 'pug' => Pug::class])]
abstract class Dog extends Animal {}
