<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Hierarchy;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(field: 'kind', map: ['dog' => Dog::class, 'cat' => Cat::class])]
abstract class Animal {}
