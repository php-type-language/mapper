<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Reader\ReaderInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

abstract class Decorator implements ProviderInterface
{
    private readonly ProviderInterface $delegate;

    public function __construct(
        ProviderInterface|ReaderInterface $delegate = new NullProvider(),
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
        return new MetadataReaderProvider($reader);
    }

    public function getClassMetadata(
        \ReflectionClass $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ClassMetadata {
        return $this->delegate->getClassMetadata($class, $types, $parser);
    }
}
