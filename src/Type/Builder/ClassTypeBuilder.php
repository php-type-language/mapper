<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Kernel\Instantiator\ClassInstantiatorInterface;
use TypeLang\Mapper\Kernel\Instantiator\DoctrineClassInstantiator;
use TypeLang\Mapper\Kernel\Instantiator\ReflectionClassInstantiator;
use TypeLang\Mapper\Kernel\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Kernel\PropertyAccessor\ReflectionPropertyAccessor;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Provider\ProviderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TObject of object = object
 * @template TResult of object|array = object|array<array-key, mixed>
 * @template-extends Builder<NamedTypeNode, TypeInterface<TResult>>
 */
abstract class ClassTypeBuilder extends Builder
{
    protected readonly ClassInstantiatorInterface $instantiator;
    protected readonly PropertyAccessorInterface $accessor;

    public function __construct(
        protected readonly ProviderInterface $driver,
        ?PropertyAccessorInterface $accessor = null,
        ?ClassInstantiatorInterface $instantiator = null,
    ) {
        $this->instantiator = $instantiator ?? $this->createDefaultClassInstantiator();
        $this->accessor = $accessor ?? $this->createDefaultPropertyAccessor();
    }

    private function createDefaultPropertyAccessor(): PropertyAccessorInterface
    {
        return new ReflectionPropertyAccessor();
    }

    private function createDefaultClassInstantiator(): ClassInstantiatorInterface
    {
        if (DoctrineClassInstantiator::isSupported()) {
            return new DoctrineClassInstantiator();
        }

        return new ReflectionClassInstantiator();
    }

    /**
     * Returns {@see true} if the type contains a reference to an existing class.
     */
    public function isSupported(TypeStatement $stmt): bool
    {
        if (!$stmt instanceof NamedTypeNode) {
            return false;
        }

        /** @var non-empty-string $name */
        $name = $stmt->name->toString();

        if (!\class_exists($name)) {
            return false;
        }

        $reflection = new \ReflectionClass($name);

        return $reflection->isInstantiable()
            // Allow abstract classes for discriminators
            || $reflection->isAbstract()
            // Allow interfaces for discriminators
            || $reflection->isInterface();
    }

    public function build(TypeStatement $stmt, BuildingContext $context): TypeInterface
    {
        $this->expectNoShapeFields($stmt);
        $this->expectNoTemplateArguments($stmt);

        /** @var class-string<TObject> $class */
        $class = $stmt->name->toString();

        return $this->create(
            metadata: $this->driver->getClassMetadata(
                class: new \ReflectionClass($class),
                context: $context,
            ),
        );
    }

    /**
     * @param ClassMetadata<TObject> $metadata
     *
     * @return TypeInterface<TResult>
     */
    abstract protected function create(ClassMetadata $metadata): TypeInterface;
}
