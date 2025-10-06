<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;
use TypeLang\Mapper\Mapping\OnTypeError;

final class ErrorMessageClassAttributeLoader extends ClassAttributeLoader
{
    public function load(\ReflectionClass $class, ClassInfo $prototype): void
    {
        $error = $this->findClassAttribute($class, OnTypeError::class);

        if ($error === null) {
            return;
        }

        $prototype->typeErrorMessage = $error->message;
    }
}
