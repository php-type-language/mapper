<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Clash;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(field: 'type', map: ['poly' => SelfSamePolygonStub::class])]
abstract class SelfSameBaseStub {}
