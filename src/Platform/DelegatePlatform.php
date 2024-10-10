<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class DelegatePlatform implements PlatformInterface
{
    /**
     * @param list<TypeBuilderInterface<covariant TypeStatement, TypeInterface>> $types
     * @param list<GrammarFeature> $features
     */
    public function __construct(
        private readonly PlatformInterface $delegate,
        private readonly array $types = [],
        private readonly array $features = [],
    ) {}

    public function getName(): string
    {
        return $this->delegate->getName();
    }

    public function getTypes(): iterable
    {
        yield from $this->types;
        yield from $this->delegate->getTypes();
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return \in_array($feature, $this->features, true)
            || $this->delegate->isFeatureSupported($feature);
    }
}
