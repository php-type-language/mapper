<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Common;

use TypeLang\Mapper\Kernel\PropertyAccessor\FastPropertyAccessor;
use TypeLang\Mapper\Kernel\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Kernel\PropertyAccessor\ReflectionPropertyAccessor;

trait SupportsPropertyAccessor
{
    private readonly PropertyAccessorInterface $propertyAccessor;

    final protected function getPropertyAccessor(): PropertyAccessorInterface
    {
        /** @phpstan-ignore-next-line : Allow instantiation outside constructor */
        return $this->propertyAccessor ??= $this->createDefaultPropertyAccessor();
    }

    final protected function bootPropertyAccessorIfNotBooted(?PropertyAccessorInterface $accessor): void
    {
        /** @phpstan-ignore-next-line : Allow instantiation outside constructor */
        $this->propertyAccessor ??= ($accessor ?? $this->createDefaultPropertyAccessor());
    }

    protected function createDefaultPropertyAccessor(): PropertyAccessorInterface
    {
        if (\PHP_VERSION_ID >= 80400) {
            return new ReflectionPropertyAccessor();
        }

        return new FastPropertyAccessor();
    }
}
