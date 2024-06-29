<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Creation\MissingTemplateArgumentsException;
use TypeLang\Mapper\Exception\Creation\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Creation\TemplateArgumentsHintNotSupportedException;
use TypeLang\Mapper\Exception\Creation\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Exception\Creation\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Exception\Creation\UnsupportedMetadataException;
use TypeLang\Mapper\Exception\Definition\InvalidTypeArgumentException;
use TypeLang\Mapper\Exception\Definition\UnsupportedAttributeException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\Meta\Reader\AttributeReader;
use TypeLang\Mapper\Type\Meta\Reader\ReaderInterface;
use TypeLang\Mapper\Type\Meta\SealedShapeFlagParameterMetadata;
use TypeLang\Mapper\Type\Meta\ShapeFieldsParameterMetadata;
use TypeLang\Mapper\Type\Meta\TemplateParameterMetadata;
use TypeLang\Mapper\Type\Meta\TypeMetadata;
use TypeLang\Mapper\Type\Meta\TypeNameParameterMetadata;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Literal\LiteralNodeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Shape\ExplicitFieldNode;
use TypeLang\Parser\Node\Stmt\Shape\ImplicitFieldNode;
use TypeLang\Parser\Node\Stmt\Template\ArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface;

/**
 * @template TInput of mixed
 * @template TOutput of mixed
 */
class NamedTypeBuilder implements TypeBuilderInterface
{
    /**
     * @var non-empty-lowercase-string
     */
    protected readonly string $lower;

    protected readonly PrinterInterface $printer;

    /**
     * @param non-empty-string $name
     * @param class-string<TypeInterface<TInput, TOutput>> $type
     */
    public function __construct(
        protected readonly string $name,
        protected readonly string $type,
        protected readonly ReaderInterface $reader = new AttributeReader(),
        PrinterInterface $printer = new PrettyPrinter(),
    ) {
        if ($printer instanceof PrettyPrinter) {
            $printer = clone $printer;
            $printer->multilineShape = \PHP_INT_MAX;
        }

        $this->printer = $printer;
        $this->lower = \strtolower($this->name);
    }

    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && $statement->name->toLowerString() === $this->lower;
    }

    /**
     * @return TypeInterface<TInput, TOutput>
     * @throws MissingTemplateArgumentsException
     * @throws ShapeFieldsNotSupportedException
     * @throws TemplateArgumentsHintNotSupportedException
     * @throws TemplateArgumentsNotSupportedException
     * @throws TooManyTemplateArgumentsException
     * @throws TypeNotFoundException
     * @throws UnsupportedMetadataException
     * @throws \ReflectionException
     * @throws InvalidTypeArgumentException
     * @throws UnsupportedAttributeException
     */
    public function build(TypeStatement $type, RegistryInterface $context): TypeInterface
    {
        assert($type instanceof NamedTypeNode);

        $metadata = $this->reader->getTypeMetadata(
            class: new \ReflectionClass($this->type),
        );

        if (!$metadata->isShapeFieldsIsAllowed() && $type->fields !== null) {
            throw ShapeFieldsNotSupportedException::fromTypeName(
                type: $type->name->toString(),
                given: $this->printer->print($type),
            );
        }

        if (!$metadata->isTemplateArgumentsIsAllowed() && $type->arguments !== null) {
            throw TemplateArgumentsNotSupportedException::fromTypeName(
                type: $type->name->toString(),
                given: $this->printer->print($type),
            );
        }

        return new $this->type(...$this->createArguments(
            metadata: $metadata,
            type: $type,
            context: $context,
        ));
    }

    /**
     * @param TypeMetadata<TypeInterface<TInput, TOutput>> $metadata
     * @return iterable<array-key, mixed>
     * @throws MissingTemplateArgumentsException
     * @throws TemplateArgumentsHintNotSupportedException
     * @throws TooManyTemplateArgumentsException
     * @throws UnsupportedMetadataException
     * @throws TypeNotFoundException
     */
    private function createArguments(
        TypeMetadata $metadata,
        NamedTypeNode $type,
        RegistryInterface $context
    ): iterable {
        $arguments = $type->arguments->items ?? [];

        $fields = null;
        $result = [];

        foreach ($metadata->getParameters() as $parameter) {
            switch (true) {
                case $parameter instanceof TemplateParameterMetadata:
                    if ($arguments === []) {
                        if ($parameter->hasDefaultValue()) {
                            $result[] = $parameter->getDefaultValue();
                            break;
                        }

                        throw MissingTemplateArgumentsException::fromTemplateArgumentsCount(
                            type: $this->printer->print($type),
                            passed: $type->arguments?->count() ?? 0,
                            expectedMin: $metadata->getNumberOfRequiredTemplateParameters(),
                            expectedMax: $metadata->getNumberOfTemplateParameters(),
                        );
                    }

                    $result[] = $this->getTemplateArgumentValue(
                        metadata: $parameter,
                        node: \array_shift($arguments),
                        context: $context,
                    );

                    break;

                case $parameter instanceof SealedShapeFlagParameterMetadata:
                    $result[] = $type->fields->sealed ?? false;
                    break;

                case $parameter instanceof ShapeFieldsParameterMetadata:
                    $result[] = ($fields ??= $this->getShapeFieldsAsArray($type, $context));
                    break;

                case $parameter instanceof TypeNameParameterMetadata:
                    $result[] = $type->name->toString();
                    break;

                default:
                    throw UnsupportedMetadataException::fromMetadataName($parameter);
            }
        }

        if ($arguments !== []) {
            throw TooManyTemplateArgumentsException::fromTemplateArgumentsCount(
                type: $this->printer->print($type),
                passed: $type->arguments?->count() ?? 0,
                expectedMin: $metadata->getNumberOfRequiredTemplateParameters(),
                expectedMax: $metadata->getNumberOfTemplateParameters(),
            );
        }

        return $result;
    }

    /**
     * @return array<array-key, TypeInterface<mixed, mixed>>
     * @throws TypeNotFoundException
     */
    private function getShapeFieldsAsArray(NamedTypeNode $type, RegistryInterface $context): array
    {
        $result = [];

        foreach ($type->fields ?? [] as $field) {
            switch (true) {
                case $field instanceof ImplicitFieldNode:
                    $result[] = $context->get($field->getType());
                    break;
                case $field instanceof ExplicitFieldNode:
                    $result[$field->getKey()] = $context->get($field->getType());
                    break;
            }
        }

        return $result;
    }

    /**
     * @throws TemplateArgumentsHintNotSupportedException
     * @throws TypeNotFoundException
     */
    private function getTemplateArgumentValue(
        TemplateParameterMetadata $metadata,
        ArgumentNode $node,
        RegistryInterface $context,
    ): mixed {
        $value = $node->value;

        if ($node->hint !== null) {
            throw TemplateArgumentsHintNotSupportedException::fromHintName(
                type: $this->name,
                argument: $this->printer->print($node->value),
                hint: $node->hint->toString(),
            );
        }

        /**
         * Returns PHP literal value from {@see LiteralNodeInterface} node.
         */
        if ($value instanceof LiteralNodeInterface) {
            return $value->getValue();
        }

        /**
         * Returns template argument as {@see string} identifier in case of
         * passed identifier is a part of allowed identifier.
         */
        if ($value instanceof NamedTypeNode && $value->name->isSimple()) {
            // Identifier string without initial namespace ("\") delimiter.
            $identifier = \ltrim($value->name->toString(), '\\');

            if (\in_array($identifier, $metadata->getAllowedIdentifiers(), true)) {
                return $value->name->getFirstPart();
            }
        }

        /**
         * Otherwise returns {@see TypeInterface} in case of given template
         * argument is a valid type name.
         */
        return $context->get($value);
    }
}
