<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Mapping\Driver\DriverInterface;
use TypeLang\Mapper\Mapping\Driver\ReflectionDriver;
use TypeLang\Mapper\Type\ObjectType;
use TypeLang\Mapper\Type\ObjectType\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Type\ObjectType\PropertyAccessor\ReflectionPropertyAccessor;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates an {@see ObjectType} from a type name containing a reference to an
 * existing class.
 *
 * @template T of object
 * @template-extends Builder<NamedTypeNode, ObjectType<T>>
 */
final class ObjectTypeBuilder extends Builder
{
    public function __construct(
        private readonly DriverInterface $driver = new ReflectionDriver(),
        private readonly PropertyAccessorInterface $accessor = new ReflectionPropertyAccessor(),
    ) {}

    /**
     * Returns {@see true} if the type contains a reference to an existing class.
     */
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && !$statement->name->isBuiltin()
            && \class_exists($statement->name->toString())
            && !\enum_exists($statement->name->toString());
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): ObjectType
    {
        self::assertNoTemplateArguments($statement);
        self::assertNoShapeFields($statement);

        /** @var class-string<T> $class */
        $class = $statement->name->toString();

        $metadata = $this->driver->getClassMetadata(
            class: new \ReflectionClass($class),
            types: $types,
        );

        return new ObjectType($metadata, $this->accessor);
    }
}
