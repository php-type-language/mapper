<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Stub;

use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperInstantiator;
use TypeLang\Mapper\Instantiator\InstantiatorInterface;
use TypeLang\Mapper\Instantiator\Strategy\InstantiationStrategyInterface;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

#[AsMapperInstantiator]
final class InstantiatorStub implements InstantiationStrategyInterface
{
    public function tryCreateInstantiator(\ReflectionClass $class, ClassMetadata $metadata): ?InstantiatorInterface
    {
        return null;
    }
}
