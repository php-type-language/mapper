<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Clash;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(field: 'type', map: ['square' => ClashSquareStub::class])]
abstract class ClashPolygonStub extends ClashBaseStub {}
