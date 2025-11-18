<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;

final class EmptyPlatform implements PlatformInterface
{
    public function getName(): string
    {
        return 'empty';
    }

    /**
     * @return array<array-key, TypeBuilderInterface>
     */
    public function getTypes(): array
    {
        return [];
    }

    /**
     * @return array<class-string<TypeInterface>, TypeCoercerInterface>
     */
    public function getTypeCoercers(): array
    {
        return [];
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return true;
    }
}
