<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Event;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

/**
 * Root of a map whose type of the data with an unknown tag is a class of
 * the map.
 */
#[DiscriminatorMap(field: 'type', map: [
    'generic' => GenericEventStub::class,
    'special' => SpecialEventStub::class,
], otherwise: GenericEventStub::class)]
abstract class EventStub {}
