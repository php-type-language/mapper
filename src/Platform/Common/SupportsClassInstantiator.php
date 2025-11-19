<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Common;

use TypeLang\Mapper\Kernel\Instantiator\ClassInstantiatorInterface;
use TypeLang\Mapper\Kernel\Instantiator\DoctrineClassInstantiator;
use TypeLang\Mapper\Kernel\Instantiator\ReflectionClassInstantiator;

trait SupportsClassInstantiator
{
    private readonly ClassInstantiatorInterface $classInstantiator;

    final protected function getClassInstantiator(): ClassInstantiatorInterface
    {
        /** @phpstan-ignore-next-line : Allow instantiation outside constructor */
        return $this->classInstantiator ??= $this->createDefaultClassInstantiator();
    }

    final protected function bootClassInstantiatorIfNotBooted(?ClassInstantiatorInterface $instantiator): void
    {
        /** @phpstan-ignore-next-line : Allow instantiation outside constructor */
        $this->classInstantiator ??= ($instantiator ?? $this->createDefaultClassInstantiator());
    }

    protected function createDefaultClassInstantiator(): ClassInstantiatorInterface
    {
        if (DoctrineClassInstantiator::isSupported()) {
            return new DoctrineClassInstantiator();
        }

        return new ReflectionClassInstantiator();
    }
}
