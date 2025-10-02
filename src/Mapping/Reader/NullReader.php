<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Info\ClassInfo;

final class NullReader implements ReaderInterface
{
    public function read(\ReflectionClass $class): ClassInfo
    {
        return new ClassInfo($class->name);
    }
}
