<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Hierarchy;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(field: 'kind', map: [1 => LevelOne::class, 2 => LevelTwo::class])]
abstract class Level {}
