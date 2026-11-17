<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Stub;

use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperTypeBuilder;

#[AsMapperTypeBuilder]
final class GlobalShoutTypeBuilderStub extends ShoutTypeBuilderStub {}
