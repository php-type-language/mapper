<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Type\ShapeType;
use TypeLang\Mapper\Type\ShapeType\Field;
use TypeLang\Type\Literal\IntLiteralNode;
use TypeLang\Type\Literal\StringLiteralNode;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\Shape\ExplicitFieldNode;
use TypeLang\Type\Shape\FieldListNode;
use TypeLang\Type\Shape\FieldNode;
use TypeLang\Type\Shape\ImplicitFieldNode;
use TypeLang\Type\Shape\NamedFieldNode;
use TypeLang\Type\Shape\ScalarFieldNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of `array{...}` and `object{...}`. A field is named
 * (`a: int`), keyed by a literal (`'a': int`, `0: int`) or implicit
 * (`array{int, string}`), its key is the next integer key as in a PHP
 * array. The rest of an unsealed shape (`...`) is `mixed`.
 *
 * @template-implements CompositeTypeBuilderInterface<array<array-key, mixed>|\stdClass>
 */
final class ShapeTypeBuilder implements CompositeTypeBuilderInterface
{
    /**
     * @throws TypeDefinitionException in case of a key of a field is not a
     *         name or a literal or is repeated
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?ShapeType
    {
        $fields = self::extractFields($node);

        if ($fields === null) {
            return null;
        }

        \assert($node instanceof NamedTypeNode);

        $name = $context->printer->print($node);
        $result = [];

        foreach (self::readFields($fields) as $key => $field) {
            if ($key === null) {
                throw TypeDefinitionException::becauseShapeKeyIsInvalid($name);
            }

            if (isset($result[$key])) {
                throw TypeDefinitionException::becauseShapeKeyIsDuplicated($name, $key);
            }

            $result[$key] = new Field(
                key: $key,
                type: $context->types->getType($field->type),
                isOptional: $field instanceof ExplicitFieldNode && $field->isOptional,
            );
        }

        $rest = null;

        if (!$fields->isSealed) {
            $rest = $context->types->getType(new NamedTypeNode(new Name('mixed')));
        }

        return new ShapeType(
            fields: \array_values($result),
            rest: $rest,
            isObject: $node->name->is('object'),
        );
    }

    /**
     * Returns the type of the field by its key as a PHP array keeps it:
     * `'1'` as `1`, an implicit field by its position. A shape has no type
     * of its keys.
     */
    public function tryGetNestedType(TypeNode $node, int|string $key, bool $isKey): ?TypeNode
    {
        $fields = self::extractFields($node);

        if ($fields === null || $isKey) {
            return null;
        }

        $key = \array_key_first([$key => true]);

        foreach (self::readFields($fields) as $current => $field) {
            if ($current === $key) {
                return $field->type;
            }
        }

        return null;
    }

    /**
     * Returns the fields of `array{...}` or `object{...}`, {@see null} in
     * case of the node is another type.
     */
    private static function extractFields(TypeNode $node): ?FieldListNode
    {
        if (!$node instanceof NamedTypeNode || $node->fields === null || $node->arguments !== null) {
            return null;
        }

        if (!$node->name->is('object') && !$node->name->is('array')) {
            return null;
        }

        return $node->fields;
    }

    /**
     * Iterates the fields by their keys, the key of an implicit field is the
     * next integer key.
     *
     * @return \Generator<int|non-empty-string|null, FieldNode> the key is
     *         {@see null} in case of it is not a name or a non-empty literal
     */
    private static function readFields(FieldListNode $fields): \Generator
    {
        $next = 0;

        foreach ($fields as $field) {
            $key = self::readKey($field, $next);

            if (\is_int($key) && $key >= $next) {
                $next = $key;

                if ($key < \PHP_INT_MAX) {
                    $next = $key + 1;
                }
            }

            yield $key => $field;
        }
    }

    /**
     * Returns the key of the field as a PHP array keeps it, `'1'` as `1`,
     * {@see null} in case of it is not a name or a non-empty literal.
     *
     * @return int|non-empty-string|null
     */
    private static function readKey(FieldNode $field, int $next): int|string|null
    {
        if ($field instanceof ImplicitFieldNode) {
            return $next;
        }

        if ($field instanceof NamedFieldNode) {
            /** @var non-empty-string */
            return $field->key->value;
        }

        if (!$field instanceof ScalarFieldNode) {
            return null;
        }

        /** @var IntLiteralNode|StringLiteralNode */
        $key = $field->key;
        $key = \array_key_first([$key->value => true]);

        if ($key === '') {
            return null;
        }

        return $key;
    }
}
