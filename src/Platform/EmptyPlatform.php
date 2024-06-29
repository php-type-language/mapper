<?php

declare(strict_types=1);

namespace Serafim\Mapper\Platform;

use Serafim\Mapper\PlatformInterface;

final class EmptyPlatform implements PlatformInterface
{
    public function getName(): string
    {
        return 'empty';
    }

    public function getBuiltinTypes(): iterable
    {
        return [];
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return true;
    }
}
