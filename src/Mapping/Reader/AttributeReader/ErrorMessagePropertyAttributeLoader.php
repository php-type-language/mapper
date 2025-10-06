<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\OnTypeError;
use TypeLang\Mapper\Mapping\OnUndefinedError;

final class ErrorMessagePropertyAttributeLoader extends PropertyAttributeLoader
{
    public function load(\ReflectionProperty $property, PropertyInfo $prototype): void
    {
        $this->loadTypeErrorMessage($property, $prototype);
        $this->loadUndefinedErrorMessage($property, $prototype);
    }

    private function loadTypeErrorMessage(\ReflectionProperty $property, PropertyInfo $prototype): void
    {
        $error = $this->findPropertyAttribute($property, OnTypeError::class);

        if ($error === null) {
            return;
        }

        $prototype->typeErrorMessage = $error->message;
    }

    private function loadUndefinedErrorMessage(\ReflectionProperty $property, PropertyInfo $prototype): void
    {
        $error = $this->findPropertyAttribute($property, OnUndefinedError::class);

        if ($error === null) {
            return;
        }

        $prototype->undefinedErrorMessage = $error->message;
    }
}
