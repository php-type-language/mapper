<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Kernel\Instantiator\ClassInstantiatorInterface;
use TypeLang\Mapper\Kernel\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Mapping\Provider\ProviderInterface;
use TypeLang\Mapper\Mapping\Reader\ReaderInterface;
use TypeLang\Mapper\Platform\Common\SupportsClassInstantiator;
use TypeLang\Mapper\Platform\Common\SupportsMetadata;
use TypeLang\Mapper\Platform\Common\SupportsPropertyAccessor;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;

use function TypeLang\Mapper\iterable_to_array;

abstract class Platform implements PlatformInterface
{
    use SupportsMetadata;
    use SupportsClassInstantiator;
    use SupportsPropertyAccessor;

    /**
     * @var list<TypeBuilderInterface>
     */
    protected readonly array $types;

    /**
     * @var array<class-string<TypeInterface>, TypeCoercerInterface>
     */
    protected readonly array $coercers;

    /**
     * @param iterable<mixed, TypeBuilderInterface> $types
     * @param iterable<class-string<TypeInterface>, TypeCoercerInterface> $coercers
     */
    public function __construct(
        ProviderInterface|ReaderInterface|null $meta = null,
        iterable $types = [],
        iterable $coercers = [],
        ?ClassInstantiatorInterface $classInstantiator = null,
        ?PropertyAccessorInterface $propertyAccessor = null,
    ) {
        $this->bootMetadataProviderIfNotBooted($meta);
        $this->bootClassInstantiatorIfNotBooted($classInstantiator);
        $this->bootPropertyAccessorIfNotBooted($propertyAccessor);

        $this->types = $this->formatTypes($types);
        $this->coercers = $this->formatCoercers($coercers);
    }

    /**
     * @param iterable<mixed, TypeBuilderInterface> $types
     *
     * @return list<TypeBuilderInterface>
     */
    protected function formatTypes(iterable $types): array
    {
        return iterable_to_array($types, false);
    }

    public function getTypes(): iterable
    {
        return $this->types;
    }

    /**
     * @param iterable<class-string<TypeInterface>, TypeCoercerInterface> $coercers
     *
     * @return array<class-string<TypeInterface>, TypeCoercerInterface>
     */
    protected function formatCoercers(iterable $coercers): array
    {
        return iterable_to_array($coercers);
    }

    public function getTypeCoercers(): iterable
    {
        foreach ($this->coercers as $type => $coercer) {
            yield $coercer => [$type];
        }
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return true;
    }
}
