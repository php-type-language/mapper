<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\AttributeDriver;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\OnTypeError;
use TypeLang\Mapper\Mapping\OnUndefinedError;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class ErrorMessagePropertyMetadataLoader extends PropertyMetadataLoader
{
    /**
     * @throws \Throwable
     */
    public function load(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        $this->loadTypeErrorMessage($property, $metadata);
        $this->loadUndefinedErrorMessage($property, $metadata);
    }

    private function loadTypeErrorMessage(\ReflectionProperty $property, PropertyMetadata $metadata): void
    {
        $attribute = $this->findPropertyAttribute($property, OnTypeError::class);

        if ($attribute === null) {
            return;
        }

        $metadata->typeErrorMessage = $attribute->message;
    }

    private function loadUndefinedErrorMessage(\ReflectionProperty $property, PropertyMetadata $metadata): void
    {
        $attribute = $this->findPropertyAttribute($property, OnUndefinedError::class);

        if ($attribute === null) {
            return;
        }

        $metadata->undefinedErrorMessage = $attribute->message;
    }
}
