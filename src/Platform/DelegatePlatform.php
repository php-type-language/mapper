<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class DelegatePlatform implements PlatformInterface
{
    /**
     * @var list<GrammarFeature>
     */
    private readonly array $features;

    /**
     * @var list<TypeBuilderInterface<covariant TypeStatement, TypeInterface>>
     */
    private readonly array $types;

    /**
     * @param iterable<array-key, TypeBuilderInterface<covariant TypeStatement, TypeInterface>> $types
     * @param iterable<array-key, GrammarFeature> $features
     */
    public function __construct(
        private readonly PlatformInterface $delegate,
        iterable $types = [],
        iterable $features = [],
    ) {
        $this->types = \array_values([...$types]);
        $this->features = \array_values([...$features]);
    }

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
