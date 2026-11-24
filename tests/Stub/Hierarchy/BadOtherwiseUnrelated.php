<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Hierarchy;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(field: 'type', map: ['x' => BadOtherwiseUnrelatedChild::class], otherwise: Unrelated::class)]
abstract class BadOtherwiseUnrelated {}
