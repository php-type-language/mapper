<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Provider\ProviderInterface;
use TypeLang\Mapper\Runtime\ClassInstantiator\ClassInstantiatorInterface;
use TypeLang\Mapper\Runtime\ClassInstantiator\CloneClassInstantiator;
use TypeLang\Mapper\Runtime\ClassInstantiator\ReflectionClassInstantiator;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Runtime\PropertyAccessor\ReflectionPropertyAccessor;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends Builder<NamedTypeNode, TypeInterface>
 */
abstract class ClassTypeBuilder extends Builder
{
    public function __construct(
        protected readonly ProviderInterface $driver,
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
    ): TypeInterface {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        /** @var class-string<T> $class */
        $class = $statement->name->toString();

        return $this->create(
            metadata: $this->driver->getClassMetadata(
                class: new \ReflectionClass($class),
                types: $types,
                parser: $parser,
            ),
        );
    }

    abstract protected function create(ClassMetadata $metadata): TypeInterface;
}
