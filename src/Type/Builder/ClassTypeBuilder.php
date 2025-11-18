<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Kernel\Instantiator\ClassInstantiatorInterface;
use TypeLang\Mapper\Kernel\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Mapping\Provider\ProviderInterface;
use TypeLang\Mapper\Type\ClassType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TObject of object = object
 * @template TResult of object|array = object|array<array-key, mixed>
 *
 * @template-extends Builder<NamedTypeNode, TypeInterface<TResult|TObject>>
 */
class ClassTypeBuilder extends Builder
{
    public function __construct(
        protected readonly ProviderInterface $meta,
        protected readonly PropertyAccessorInterface $accessor,
        protected readonly ClassInstantiatorInterface $instantiator,
    ) {}

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

    public function build(TypeStatement $stmt, BuildingContext $context): ClassType
    {
        $this->expectNoShapeFields($stmt);
        $this->expectNoTemplateArguments($stmt);

        /** @var class-string<TObject> $class */
        $class = $stmt->name->toString();

        /** @var ClassType<TObject, TResult> */
        return new ClassType(
            metadata: $this->meta->getClassMetadata(
                class: new \ReflectionClass($class),
                context: $context,
            ),
            accessor: $this->accessor,
            instantiator: $this->instantiator,
        );
    }
}
