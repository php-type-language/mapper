<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Hierarchy;

use TypeLang\Mapper\Mapping\DiscriminatorMap;

#[DiscriminatorMap(field: 'type', map: ['pdf' => Pdf::class, 'txt' => Txt::class], otherwise: Txt::class)]
abstract class Doc {}
