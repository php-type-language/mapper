<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

final class EmptyPlatform implements PlatformInterface
{
    public function getName(): string
    {
        return 'empty';
    }

    public function getTypes(): iterable
    {
        return [];
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return true;
    }
}
