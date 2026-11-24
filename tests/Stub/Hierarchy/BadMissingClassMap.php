<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Hierarchy;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(field: 'type', map: ['x' => 'TypeLang\Mapper\Tests\Stub\Hierarchy\NoSuchClass'])]
abstract class BadMissingClassMap {}
