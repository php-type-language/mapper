<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\Info\ClassInfo;
use TypeLang\Mapper\Mapping\MapName;

final class AliasClassAttributeLoader extends ClassAttributeLoader
{
    public function load(\ReflectionClass $class, ClassInfo $info): void
    {
        $attribute = $this->findClassAttribute($class, MapName::class);

        if ($attribute === null) {
            return;
        }
    }
}
