<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Type\Check\IntMaskCheck;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Mapper\Type\LiteralType;
use TypeLang\Mapper\Type\RefinedType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnionType;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\Template\TypedTemplateArgumentNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of the flags: an integer made of the bits of the listed
 * values only.
 *
 * ```
 * int-mask<1, 2, 4>
 * int-mask-of<1|2|4>
 * int-mask-of<Access::FLAG_*>
 * ```
 *
 * Each argument is an integer, a constant of one or a union of them, a mask
 * of constants too.
 *
 * @template-implements TypeBuilderInterface<int>
 */
final class IntMaskTypeBuilder implements TypeBuilderInterface
{
    /**
     * Whether the name takes one argument only, by the names in lower case
     */
    private const NAMES = [
        'int-mask' => false,
        'int-mask-of' => true,
    ];

    public function __construct(
        /**
         * Type the mask refines
         *
         * @var TypeInterface<int>
         */
        private readonly TypeInterface $type = new IntType(),
    ) {}

    /**
     * @return TypeInterface<int>|null
     * @throws TypeDefinitionException in case of an argument is not made of
     *         non-negative integers
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface
    {
        if (!$node instanceof NamedTypeNode || $node->arguments === null || $node->fields !== null) {
            return null;
        }

        $isSingle = self::NAMES[$node->name->toLowerString()] ?? null;

        if ($isSingle === null || ($isSingle && \count($node->arguments) !== 1)) {
            return null;
        }

        $name = $context->printer->print($node);
        $mask = 0;

        foreach ($node->arguments as $argument) {
            if (!$argument instanceof TypedTemplateArgumentNode) {
                throw TypeDefinitionException::becauseMaskValueIsInvalid($name);
            }

            foreach (self::getValues($context->types->getType($argument->value), $name) as $value) {
                $mask |= $value;
            }
        }

        return new RefinedType($this->type, [new IntMaskCheck($mask)]);
    }

    /**
     * Returns the integers the built type of an argument is made of.
     *
     * @param TypeInterface<mixed> $type
     * @param non-empty-string $name
     * @return list<int<0, max>>
     * @throws TypeDefinitionException in case of the type is not a
     *         non-negative integer or a union of them
     */
    private static function getValues(TypeInterface $type, string $name): array
    {
        if ($type instanceof LiteralType && \is_int($type->value) && $type->value >= 0) {
            return [$type->value];
        }

        if (!$type instanceof UnionType) {
            throw TypeDefinitionException::becauseMaskValueIsInvalid($name);
        }

        $values = [];

        foreach ($type->types as $item) {
            foreach (self::getValues($item, $name) as $value) {
                $values[] = $value;
            }
        }

        return $values;
    }
}
