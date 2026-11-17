<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(field: '', map: [])]
abstract class BrokenDiscriminatorStub {}
