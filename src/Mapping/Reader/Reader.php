<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassPrototype;

abstract class Reader implements ReaderInterface
{
    public function __construct(
        private readonly ReaderInterface $delegate = new NullReader(),
    ) {}

    public function read(\ReflectionClass $class): ClassPrototype
    {
        return $this->delegate->read($class);
    }
}
