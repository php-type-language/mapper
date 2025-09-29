<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\ClassInstantiator;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Context;

final class CloneClassInstantiator implements ClassInstantiatorInterface
{
    /**
     * @var array<class-string, object>
     */
    private array $classes;

    public function __construct(
        private readonly ClassInstantiatorInterface $delegate,
    ) {}

    public function instantiate(string $class): object
    {
        if (isset($this->classes[$class])) {
            return clone $this->classes[$class];
        }

        return $this->classes[$class] = $this->delegate->instantiate($class);
    }
}
