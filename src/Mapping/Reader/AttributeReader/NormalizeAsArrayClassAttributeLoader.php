<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;
use TypeLang\Mapper\Mapping\NormalizeAsArray;

final class NormalizeAsArrayClassAttributeLoader extends ClassAttributeLoader
{
    public function load(\ReflectionClass $class, ClassInfo $prototype): void
    {
        $attribute = $this->findClassAttribute($class, NormalizeAsArray::class);

        if ($attribute === null) {
            return;
        }

        $prototype->isNormalizeAsArray = $attribute->enabled;
    }
}
