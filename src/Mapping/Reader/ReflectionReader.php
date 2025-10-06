<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Reader\ReflectionReader\ClassReflectionLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader\DefaultValueReflectionLoader;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader\PropertyReflectionLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader\TypePropertyReflectionLoader;

/**
 * @template-extends MetadataReader<ClassReflectionLoaderInterface, PropertyReflectionLoaderInterface>
 */
final class ReflectionReader extends MetadataReader
{
    public function __construct(
        ReaderInterface $delegate = new NullReader(),
    ) {
        parent::__construct($delegate);
    }

    /**
     * @return list<PropertyReflectionLoaderInterface>
     */
    #[\Override]
    protected function createPropertyLoaders(): array
    {
        return [
            new TypePropertyReflectionLoader(),
            new DefaultValueReflectionLoader(),
        ];
    }
}
