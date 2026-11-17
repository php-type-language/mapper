<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Type\LiteralType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnionType;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\Template\TypedTemplateArgumentNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of `value-of<T>` of a backed enum: one of the values of
 * its cases, kept as a scalar and not turned into the case.
 *
 * ```
 * value-of<Status> // 'active'|'blocked'
 * ```
 *
 * The other arguments of `value-of`, such as a constant holding an array,
 * are left to the other builders.
 *
 * @template-implements TypeBuilderInterface<int|string>
 */
final class ValueOfTypeBuilder implements TypeBuilderInterface
{
    /**
     * @return TypeInterface<int|string>|null
     * @throws TypeDefinitionException in case of the type of the values of
     *         the enum cannot be built
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface
    {
        if (
            !$node instanceof NamedTypeNode
            || $node->fields !== null
            || $node->arguments === null
            || \count($node->arguments) !== 1
            || $node->name->toLowerString() !== 'value-of'
        ) {
            return null;
        }

        $argument = $node->arguments[0];

        if (!$argument instanceof TypedTemplateArgumentNode) {
            return null;
        }

        $enum = $argument->value;

        if (!$enum instanceof NamedTypeNode || $enum->arguments !== null || $enum->fields !== null) {
            return null;
        }

        $class = \ltrim($enum->name->toString(), '\\');

        if (!\enum_exists($class) || !\is_a($class, \BackedEnum::class, true)) {
            return null;
        }

        $backing = (string) (new \ReflectionEnum($class))->getBackingType();

        if ($backing === '') {
            return null;
        }

        /** @var TypeInterface<int|string> */
        $type = $context->types->getType(new NamedTypeNode(new Name($backing)));
        $types = [];

        foreach ($class::cases() as $case) {
            /** @var non-empty-string */
            $literal = \var_export($case->value, true);

            $types[] = new LiteralType($case->value, $type, $literal);
        }

        if ($types === []) {
            return null;
        }

        if (\count($types) === 1) {
            return $types[0];
        }

        return new UnionType($types, $context->printer->print($node));
    }
}
