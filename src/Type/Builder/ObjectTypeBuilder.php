<?php

declare(strict_types=1);

namespace Serafim\Mapper\Type\Builder;

use Serafim\Mapper\Exception\Creation\ShapeFieldsNotSupportedException;
use Serafim\Mapper\Exception\Creation\TemplateArgumentsNotSupportedException;
use Serafim\Mapper\Meta\Reader\AttributeReader;
use Serafim\Mapper\Meta\Reader\InMemoryReader;
use Serafim\Mapper\Meta\Reader\ReaderInterface;
use Serafim\Mapper\Registry\RegistryInterface;
use Serafim\Mapper\Type\ObjectType;
use Serafim\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface;

/**
 * Creates an {@see ObjectType} from a type name containing a reference to an
 * existing class.
 */
final class ObjectTypeBuilder implements TypeBuilderInterface
{
    private readonly ReaderInterface $reader;

    public function __construct(
        ReaderInterface $reader = new AttributeReader(),
        private readonly PrinterInterface $printer = new PrettyPrinter(),
    ) {
        $this->reader = new InMemoryReader($reader);
    }

    /**
     * Returns {@see true} if the type contains a reference to an existing class.
     */
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && !$statement->name->isBuiltin()
            && \class_exists($statement->name->toString());
    }

    /**
     * Returns the {@see ObjectType} from the class reference.
     *
     * Please note that objects do not (yet) support template
     * arguments and shape fields.
     */
    public function build(TypeStatement $type, RegistryInterface $context): TypeInterface
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

        return new ObjectType($this->reader->getClassMetadata(
            class: new \ReflectionClass($type->name->toString()),
            types: $context,
        ));
    }
}
