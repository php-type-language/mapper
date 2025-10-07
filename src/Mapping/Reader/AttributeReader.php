<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Reader\AttributeReader\AliasPropertyAttributeLoader;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\ClassAttributeLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\DiscriminatorMapClassAttributeLoader;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\ErrorMessageClassAttributeLoader;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\ErrorMessagePropertyAttributeLoader;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\NormalizeAsArrayClassAttributeLoader;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\PropertyAttributeLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\SkipConditionsPropertyAttributeLoader;
use TypeLang\Mapper\Mapping\Reader\AttributeReader\TypePropertyAttributeLoader;

/**
 * @template-extends MetadataReader<ClassAttributeLoaderInterface, PropertyAttributeLoaderInterface>
 */
class AttributeReader extends MetadataReader
{
    /**
     * @return list<ClassAttributeLoaderInterface>
     */
    #[\Override]
    protected function createClassLoaders(): array
    {
        return [
            new NormalizeAsArrayClassAttributeLoader(),
            new DiscriminatorMapClassAttributeLoader(),
            new ErrorMessageClassAttributeLoader(),
        ];
    }

    /**
     * @return list<PropertyAttributeLoaderInterface>
     */
    #[\Override]
    protected function createPropertyLoaders(): array
    {
        return [
            new TypePropertyAttributeLoader(),
            new AliasPropertyAttributeLoader(),
            new ErrorMessagePropertyAttributeLoader(),
            new SkipConditionsPropertyAttributeLoader(),
        ];
    }
}
