<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Hierarchy;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(field: 'type', map: ['special' => SpecialMsg::class], otherwise: PlainMsg::class)]
abstract class Msg {}
