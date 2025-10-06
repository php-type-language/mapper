<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Mapping\Provider\ProviderInterface;
use TypeLang\Mapper\Mapping\Provider\ReflectionDriver;
use TypeLang\Mapper\Runtime\ClassInstantiator\ClassInstantiatorInterface;
use TypeLang\Mapper\Runtime\ClassInstantiator\CloneClassInstantiator;
use TypeLang\Mapper\Runtime\ClassInstantiator\ReflectionClassInstantiator;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Runtime\PropertyAccessor\ReflectionPropertyAccessor;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\ClassType;
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
        protected readonly ProviderInterface $driver = new ReflectionDriver(),
        protected readonly PropertyAccessorInterface $accessor = new ReflectionPropertyAccessor(),
        protected readonly ClassInstantiatorInterface $instantiator = new CloneClassInstantiator(
            delegate: new ReflectionClassInstantiator(),
        ),
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

        return new ClassType(
            metadata: $this->driver->getClassMetadata(
                class: new \ReflectionClass($class),
                types: $types,
                parser: $parser,
            ),
            accessor: $this->accessor,
            instantiator: $this->instantiator,
        );
    }
}
