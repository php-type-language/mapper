<?php

declare(strict_types=1);

namespace Serafim\Mapper;

use Serafim\Mapper\Platform\GrammarFeature;
use Serafim\Mapper\Type\Builder\TypeBuilderInterface;

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
