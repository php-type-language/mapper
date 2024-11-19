<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\InternalTypeException;
use TypeLang\Mapper\Mapping\Driver\DriverInterface;
use TypeLang\Mapper\Mapping\Driver\ReflectionDriver;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\ClassType;
use TypeLang\Mapper\Type\ClassType\ClassInstantiator\ClassInstantiatorInterface;
use TypeLang\Mapper\Type\ClassType\ClassInstantiator\ReflectionClassInstantiator;
use TypeLang\Mapper\Type\ClassType\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Type\ClassType\PropertyAccessor\ReflectionPropertyAccessor;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates an {@see ClassType} from a type name containing a reference to an
 * existing class.
 *
 * @template T of object
 * @template-extends Builder<NamedTypeNode, ClassType<T>>
 */
class ClassTypeBuilder extends Builder
{
    public function __construct(
        protected readonly DriverInterface $driver = new ReflectionDriver(),
        protected readonly PropertyAccessorInterface $accessor = new ReflectionPropertyAccessor(),
        protected readonly ClassInstantiatorInterface $instantiator = new ReflectionClassInstantiator(),
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

        return $reflection->isInstantiable()
            // Allow abstract classes for discriminators
            || $reflection->isAbstract()
            // Allow interfaces for discriminators
            || $reflection->isInterface();
    }

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ClassType {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        /** @var class-string<T> $class */
        $class = $statement->name->toString();

        $reflection = new \ReflectionClass($class);

        $metadata = $this->driver->getClassMetadata(
            class: $reflection,
            types: $types,
            parser: $parser,
        );

        $discriminator = $metadata->findDiscriminator();

        if ($discriminator === null && !$reflection->isInstantiable()) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $statement,
                message: \vsprintf('%s "%s" expects a discriminator map to be able to be created', [
                    match (true) {
                        $reflection->isAbstract() => 'Non-creatable abstract class',
                        $reflection->isInterface() => 'Non-creatable interface',
                        default => 'Unknown non-instantiable type',
                    },
                    $reflection->getName(),
                ]),
            );
        }

        return new ClassType(
            metadata: $metadata,
            accessor: $this->accessor,
            instantiator: $this->instantiator,
        );
    }
}
