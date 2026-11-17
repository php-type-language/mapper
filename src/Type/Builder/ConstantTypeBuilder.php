<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Type\LiteralType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnionType;
use TypeLang\Type\Constant\ConstMaskTypeNode;
use TypeLang\Type\Constant\ConstTypeNode;
use TypeLang\Type\Constant\MaskNode;
use TypeLang\Type\Constant\MaskNode\WildcardMaskNode;
use TypeLang\Type\Identifier;
use TypeLang\Type\Literal\NullLiteralNode;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\NamespaceName;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of a constant (`Foo::BAR`) and of a mask of constants
 * (`Foo::BAR_*`, `Foo::*`, `E_*`, `Some\Any\BAR_*`). A constant is the
 * literal of its value: a scalar, `null` or a case of an enum. A mask is
 * the union of the literals of the matched constants, the strings are
 * coerced to last as in a union.
 *
 * @template-implements TypeBuilderInterface<mixed>
 */
final class ConstantTypeBuilder implements TypeBuilderInterface
{
    /**
     * @return TypeInterface<mixed>|null
     * @throws TypeDefinitionException in case of no constant is found, its
     *         class is relative to the declaration or its value is not a
     *         scalar, `null` or a case of an enum
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface
    {
        if (!$node instanceof ConstTypeNode && !$node instanceof ConstMaskTypeNode) {
            return null;
        }

        $name = $context->printer->print($node);

        $values = self::getValues($node, $name);

        if ($values === []) {
            throw TypeDefinitionException::becauseConstantIsNotFound($name);
        }

        $types = $strings = $others = [];

        foreach ($values as $value) {
            $type = self::createType($value, $name, $context);
            $types[] = $type;

            if (\is_string($value)) {
                $strings[] = $type;

                continue;
            }

            $others[] = $type;
        }

        if (\count($types) === 1) {
            return $types[0];
        }

        /** @var non-empty-list<TypeInterface<mixed>> */
        $order = [...$others, ...$strings];

        return new UnionType($types, $name, $order);
    }

    /**
     * Returns the distinct values of the constants, empty in case of none.
     *
     * @param non-empty-string $name
     * @return list<mixed>
     * @throws TypeDefinitionException in case of the class is relative to the
     *         declaration
     */
    private static function getValues(ConstTypeNode|ConstMaskTypeNode $node, string $name): array
    {
        $owner = $node->owner;

        if ($owner instanceof Name && $owner->isSpecial()) {
            throw TypeDefinitionException::becauseConstantClassIsRelative($name);
        }

        if ($node instanceof ConstTypeNode) {
            return self::getConstantValues($node->owner, $node->constant);
        }

        $pattern = self::createPattern($node->mask);
        $values = [];

        foreach (self::getCandidates($owner) as $constant => $value) {
            if (\preg_match($pattern, (string) $constant) === 1 && !\in_array($value, $values, true)) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * @return list<mixed>
     */
    private static function getConstantValues(Name|NamespaceName $owner, Identifier $constant): array
    {
        $owner = \ltrim($owner->toString(), '\\');

        if (!\class_exists($owner) && !\interface_exists($owner)) {
            $global = $owner . '\\' . $constant->value;

            if (\defined($global)) {
                return [\constant($global)];
            }

            return [];
        }

        $class = new \ReflectionClass($owner);

        if (!$class->hasConstant($constant->value)) {
            return [];
        }

        return [$class->getConstant($constant->value)];
    }

    /**
     * Returns the constants a mask of the owner is matched with by the names
     * written after the owner: of the class, of the namespace or global.
     *
     * @return array<array-key, mixed>
     */
    private static function getCandidates(Name|NamespaceName|null $owner): array
    {
        $prefix = '';

        if ($owner !== null) {
            $prefix = \ltrim($owner->toString(), '\\');
        }

        if ($owner instanceof Name && (\class_exists($prefix) || \interface_exists($prefix))) {
            return (new \ReflectionClass($prefix))->getConstants();
        }

        $result = [];

        foreach (\get_defined_constants() as $constant => $value) {
            $separator = \strrpos($constant, '\\');
            $namespace = '';

            if ($separator !== false) {
                $namespace = \substr($constant, 0, $separator);
                $constant = \substr($constant, $separator + 1);
            }

            if (\strcasecmp($namespace, $prefix) === 0) {
                $result[$constant] = $value;
            }
        }

        return $result;
    }

    /**
     * @return non-empty-string
     */
    private static function createPattern(MaskNode $mask): string
    {
        $pattern = '';

        foreach ($mask->children as $item) {
            if ($item instanceof WildcardMaskNode) {
                $pattern .= '.*';

                continue;
            }

            $pattern .= \preg_quote($item->value, '/');
        }

        return '/^' . $pattern . '$/D';
    }

    /**
     * @param non-empty-string $name
     * @return TypeInterface<mixed>
     * @throws TypeDefinitionException in case of the value is not a scalar,
     *         `null` or a case of an enum
     */
    private static function createType(mixed $value, string $name, BuildContext $context): TypeInterface
    {
        if ($value === null) {
            return $context->types->getType(new NullLiteralNode());
        }

        if ($value instanceof \UnitEnum) {
            /** @var TypeInterface<\UnitEnum> */
            $type = $context->types->getType(new NamedTypeNode(Name::from($value::class)));

            return new LiteralType($value, $type, $name);
        }

        if (\is_int($value) || \is_float($value) || \is_string($value) || \is_bool($value)) {
            /** @var TypeInterface<int|float|string|bool> */
            $type = $context->types->getType(new NamedTypeNode(new Name(\get_debug_type($value))));

            return new LiteralType($value, $type, $name);
        }

        throw TypeDefinitionException::becauseConstantValueIsNotSupported($name, \get_debug_type($value));
    }
}
