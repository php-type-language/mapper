<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Shape;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

/**
 * Concrete base mapping the data with an unknown tag to itself.
 */
#[DiscriminatorMap(field: 'kind', map: ['labeled' => LabeledStub::class], otherwise: FallbackShapeStub::class)]
class FallbackShapeStub
{
    public string $label = 'none';
}
