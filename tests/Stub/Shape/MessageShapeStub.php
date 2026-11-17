<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Shape;

use TypeLang\Mapper\Mapping\DiscriminatorMap;
use TypeLang\Mapper\Mapping\OnTypeError;

#[DiscriminatorMap(field: 'type', map: ['dot' => DotStub::class])]
#[OnTypeError('Shape is expected, {{value}} given')]
abstract class MessageShapeStub {}
