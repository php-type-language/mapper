<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use TypeLang\Mapper\Platform\GrammarFeature;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;

interface PlatformInterface
{
    /**
     * @return non-empty-string
     */
    public function getName(): string;

    /**
     * @return iterable<array-key, TypeBuilderInterface>
     */
    public function getBuiltinTypes(): iterable;

    /**
     * Returns {@see true} in case of feature is supported.
     */
    public function isFeatureSupported(GrammarFeature $feature): bool;
}
