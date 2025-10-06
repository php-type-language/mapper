<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassPrototype;

final class NullReader implements ReaderInterface
{
    public function read(\ReflectionClass $class): ClassPrototype
    {
        return new ClassPrototype($class->name);
    }
}
