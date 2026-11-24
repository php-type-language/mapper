<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Otherwise;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

/**
 * Base mapping the data with an unknown tag to a child class.
 */
#[DiscriminatorMap(
    field: 'type',
    map: ['mapped' => OtherwiseMappedStub::class],
    otherwise: OtherwiseFallbackStub::class,
)]
abstract class OtherwiseBaseStub {}
