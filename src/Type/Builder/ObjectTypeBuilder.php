<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Creation\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Creation\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver;
use TypeLang\Mapper\Mapping\Driver\DriverInterface;
use TypeLang\Mapper\Mapping\Driver\InMemoryDriver;
use TypeLang\Mapper\Type\ObjectType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface;

/**
 * Creates an {@see ObjectType} from a type name containing a reference to an
 * existing class.
 *
 * @template T of object
 */
final class ObjectTypeBuilder implements TypeBuilderInterface
{
    private readonly DriverInterface $reader;

    public function __construct(
        DriverInterface $reader = new AttributeDriver(),
        private readonly PrinterInterface $printer = new PrettyPrinter(),
    ) {
        $this->reader = new InMemoryDriver($reader);
    }

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

    /**
     * Returns the {@see ObjectType} from the class reference.
     *
     * Please note that objects do not (yet) support template
     * arguments and shape fields.
     *
     * @return ObjectType<T>
     * @throws ShapeFieldsNotSupportedException
     * @throws TemplateArgumentsNotSupportedException
     * @throws \ReflectionException
     */
    public function build(TypeStatement $type, RepositoryInterface $context): ObjectType
    {
        assert($type instanceof NamedTypeNode);

        if ($type->fields !== null) {
            throw ShapeFieldsNotSupportedException::fromTypeName(
                type: $type->name->toString(),
                given: $this->printer->print($type),
            );
        }

        if ($type->arguments !== null) {
            throw TemplateArgumentsNotSupportedException::fromTypeName(
                type: $type->name->toString(),
                given: $this->printer->print($type),
            );
        }

        /** @var class-string<T> $class */
        $class = $type->name->toString();

        /** @var ObjectType<T> */
        return new ObjectType($this->reader->getClassMetadata(
            class: new \ReflectionClass($class),
            types: $context,
        ));
    }
}
