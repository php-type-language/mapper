<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\TypeInterface;

final class EmptyPlatform implements PlatformInterface
{
    public function getName(): string
    {
        return 'empty';
    }

    /**
     * @return array<class-string<TypeInterface>, TypeCoercerInterface>
     */
    public function getTypeCoercers(Direction $direction): array
    {
        return [];
    }

    /**
     * @return array<array-key, TypeBuilderInterface>
     */
    public function getTypes(Direction $direction): array
    {
        return [];
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return true;
    }
}
