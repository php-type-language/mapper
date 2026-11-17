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
 * @template-implements TypeBuilderInterface<array<array-key, mixed>|\stdClass>
 */
final class ShapeTypeBuilder implements TypeBuilderInterface
{
    /**
     * @throws TypeDefinitionException in case of a key of a field is not a
     *         name or a literal or is repeated
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?ShapeType
    {
        if (
            !$node instanceof NamedTypeNode
            || $node->fields === null
            || $node->arguments !== null
            || $node->name->namespace !== null
        ) {
            return null;
        }

        $kind = $node->name->toLowerString();

        if ($kind !== 'array' && $kind !== 'object') {
            return null;
        }

        $name = $context->printer->print($node);

        $fields = [];
        $next = 0;

        foreach ($node->fields as $field) {
            $key = self::readKey($name, $field, $next);

            if (\is_int($key) && $key >= $next) {
                $next = $key;

                if ($key < \PHP_INT_MAX) {
                    $next = $key + 1;
                }
            }

            if (isset($fields[$key])) {
                throw TypeDefinitionException::becauseShapeKeyIsDuplicated($name, $key);
            }

            $fields[$key] = new Field(
                key: $key,
                type: $context->types->getType($field->type),
                isOptional: $field instanceof ExplicitFieldNode && $field->isOptional,
            );
        }

        $rest = null;

        if (!$node->fields->isSealed) {
            $rest = $context->types->getType(new NamedTypeNode(new Name('mixed')));
        }

        return new ShapeType(
            fields: \array_values($fields),
            name: $name,
            rest: $rest,
            isObject: $kind === 'object',
        );
    }

    /**
     * Returns the key of the field as a PHP array keeps it, `'1'` as `1`.
     *
     * @param non-empty-string $shape
     * @return int|non-empty-string
     * @throws TypeDefinitionException in case of the key is not a name or a
     *         non-empty literal
     */
    private static function readKey(string $shape, FieldNode $field, int $next): int|string
    {
        if ($field instanceof ImplicitFieldNode) {
            return $next;
        }

        if ($field instanceof NamedFieldNode) {
            /** @var non-empty-string */
            return $field->key->value;
        }

        if ($field instanceof ScalarFieldNode) {
            /** @var IntLiteralNode|StringLiteralNode */
            $key = $field->key;
            $key = \array_key_first([$key->value => true]);

            if ($key !== '') {
                return $key;
            }
        }

        throw TypeDefinitionException::becauseShapeKeyIsInvalid($shape);
    }
}
