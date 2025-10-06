<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\Metadata\ClassPrototype;
use TypeLang\Mapper\Mapping\OnTypeError;

final class ErrorMessageClassAttributeLoader extends ClassAttributeLoader
{
    public function load(\ReflectionClass $class, ClassPrototype $prototype): void
    {
        $error = $this->findClassAttribute($class, OnTypeError::class);

        if ($error === null) {
            return;
        }

        $prototype->typeErrorMessage = $error->message;
    }
}
