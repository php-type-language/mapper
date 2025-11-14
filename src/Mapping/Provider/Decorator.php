<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Reader\ReaderInterface;

abstract class Decorator implements ProviderInterface
{
    private readonly ProviderInterface $delegate;

    public function __construct(
        ProviderInterface|ReaderInterface $delegate = new MetadataBuilder(),
    ) {
        $this->delegate = $this->createProvider($delegate);
    }

    private function createProvider(ProviderInterface|ReaderInterface $readerOrProvider): ProviderInterface
    {
        if ($readerOrProvider instanceof ReaderInterface) {
            return $this->createDefaultProvider($readerOrProvider);
        }

        return $readerOrProvider;
    }

    private function createDefaultProvider(ReaderInterface $reader): ProviderInterface
    {
        return new MetadataBuilder($reader);
    }

    public function getClassMetadata(\ReflectionClass $class, BuildingContext $context): ClassMetadata
    {
        return $this->delegate->getClassMetadata($class, $context);
    }
}
