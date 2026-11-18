<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Type\DateTimeType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\Literal\StringLiteralNode;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\Template\TypedTemplateArgumentNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of {@see \DateTimeInterface} and its implementations
 * with an optional format: `DateTimeImmutable<'Y-m-d'>`. An interface is
 * created as {@see \DateTimeImmutable}, the strings are denormalized by the
 * type of `string` from the repository.
 *
 * @template-implements TypeBuilderInterface<\DateTimeInterface>
 */
final class DateTimeTypeBuilder implements TypeBuilderInterface
{
    /**
     * @return DateTimeType<\DateTimeInterface>|null
     * @throws TypeDefinitionException in case of the format is not a single
     *         non-empty string literal
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?DateTimeType
    {
        if (!$node instanceof NamedTypeNode || $node->fields !== null) {
            return null;
        }

        $name = $node->name->toClassString();

        if (
            $name === null
            || !(\class_exists($name) || \interface_exists($name))
            || !\is_a($name, \DateTimeInterface::class, true)
        ) {
            return null;
        }

        $type = (new \ReflectionClass($name))->name;

        $printed = $context->printer->print($node);

        /** @var TypeInterface<string> */
        $string = $context->types->getType(new NamedTypeNode(new Name('string')));

        return new DateTimeType(
            class: $type,
            implementation: self::findClass($type)
                ?? throw TypeDefinitionException::becauseTypeIsNotSupported($printed),
            string: $string,
            format: self::readFormat($node, $printed),
        );
    }

    /**
     * Returns the class a date of the type is created by, {@see null} in case
     * of the type is neither {@see \DateTime} nor {@see \DateTimeImmutable}
     * nor an interface both implement.
     *
     * @param class-string<\DateTimeInterface> $type
     * @return class-string<\DateTime|\DateTimeImmutable>|null
     */
    private static function findClass(string $type): ?string
    {
        $isDate = \is_a($type, \DateTimeImmutable::class, true) || \is_a($type, \DateTime::class, true);

        if ($isDate && (new \ReflectionClass($type))->isInstantiable()) {
            return $type;
        }

        if (!$isDate && \is_a(\DateTimeImmutable::class, $type, true)) {
            return \DateTimeImmutable::class;
        }

        return null;
    }

    /**
     * @param non-empty-string $printed
     * @return non-empty-string|null
     * @throws TypeDefinitionException in case of the format is not a single
     *         non-empty string literal
     */
    private static function readFormat(NamedTypeNode $node, string $printed): ?string
    {
        $arguments = $node->arguments->children ?? [];

        if ($arguments === []) {
            return null;
        }

        $format = $arguments[0];

        if (
            \count($arguments) === 1
            && $format instanceof TypedTemplateArgumentNode
            && $format->variance === null
            && $format->value instanceof StringLiteralNode
            && $format->value->value !== ''
        ) {
            return $format->value->value;
        }

        throw TypeDefinitionException::becauseDateFormatIsInvalid($printed);
    }
}
