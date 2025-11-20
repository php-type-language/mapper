<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

final class EmptyPlatform implements PlatformInterface
{
    public function getName(): string
    {
        return 'empty';
    }

    /**
     * @return array{}
     */
    public function getTypes(): array
    {
        return [];
    }

    /**
     * @return array{}
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
