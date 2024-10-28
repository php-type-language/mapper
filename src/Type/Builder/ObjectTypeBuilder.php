<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Mapping\Driver\DriverInterface;
use TypeLang\Mapper\Mapping\Driver\ReflectionDriver;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\ObjectType;
use TypeLang\Mapper\Type\ObjectType\ObjectInstantiator\ObjectInstantiatorInterface;
use TypeLang\Mapper\Type\ObjectType\ObjectInstantiator\ReflectionObjectInstantiator;
use TypeLang\Mapper\Type\ObjectType\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Type\ObjectType\PropertyAccessor\ReflectionPropertyAccessor;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates an {@see ObjectType} from a type name containing a reference to an
 * existing class.
 *
 * @template T of object
 * @template-extends Builder<NamedTypeNode, ObjectType<T>>
 */
class ObjectTypeBuilder extends Builder
{
    public function __construct(
        protected readonly DriverInterface $driver = new ReflectionDriver(),
        protected readonly PropertyAccessorInterface $accessor = new ReflectionPropertyAccessor(),
        protected readonly ObjectInstantiatorInterface $instantiator = new ReflectionObjectInstantiator(),
    ) {}

    /**
     * Returns {@see true} if the type contains a reference to an existing class.
     */
    public function isSupported(TypeStatement $statement): bool
    {
        if (!$statement instanceof NamedTypeNode) {
            return false;
        }

        /** @var non-empty-string $name */
        $name = $statement->name->toString();

        if (!\class_exists($name)) {
            return false;
        }

        $reflection = new \ReflectionClass($name);

        return $reflection->isInstantiable();
    }

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ObjectType {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        /** @var class-string<T> $class */
        $class = $statement->name->toString();

        $metadata = $this->driver->getClassMetadata(
            class: new \ReflectionClass($class),
            types: $types,
            parser: $parser,
        );

        return new ObjectType(
            metadata: $metadata,
            accessor: $this->accessor,
            instantiator: $this->instantiator,
        );
    }
}
