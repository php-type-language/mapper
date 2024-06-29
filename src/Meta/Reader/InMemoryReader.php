<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Meta\Reader;

use TypeLang\Mapper\Meta\ClassMetadata;
use TypeLang\Mapper\Registry\RegistryInterface;

final class InMemoryReader extends Reader
{
    /**
     * @var array<class-string, ClassMetadata>
     */
    private array $types = [];

    public function __construct(
        private readonly ReaderInterface $delegate,
    ) {}

    public function getClassMetadata(\ReflectionClass $class, RegistryInterface $types): ClassMetadata
    {
        return $this->types[$class->getName()] ??= $this->delegate->getClassMetadata($class, $types);
    }
}
