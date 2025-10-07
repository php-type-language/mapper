<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;

class NullReader implements ReaderInterface
{
    public function read(\ReflectionClass $class, TypeParserInterface $parser): ClassInfo
    {
        return new ClassInfo($class->name);
    }
}
