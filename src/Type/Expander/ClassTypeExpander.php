<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Expander;

use TypeLang\Mapper\Context\ExpandContext;
use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Type\ClassType;
use TypeLang\Mapper\Type\ClassType\Field;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\Identifier;
use TypeLang\Type\Literal\StringLiteralNode;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\Shape\FieldListNode;
use TypeLang\Type\Shape\NamedFieldNode;
use TypeLang\Type\Shape\ScalarFieldNode;
use TypeLang\Type\TypeNode;

/**
 * Expands a class into `object` with the fields of the written properties
 * by their names, a field the data may omit is optional.
 *
 * ```
 * // object{id: int, status: 'new'|'paid', role?: string}
 * ```
 */
final class ClassTypeExpander implements TypeExpanderInterface
{
    public function tryExpand(TypeInterface $type, ExpandContext $context): ?TypeNode
    {
        if (!$type instanceof ClassType) {
            return null;
        }

        $fields = [];

        foreach ($type->writable as $property) {
            $fields[] = $this->getField($property, $context);
        }

        return new NamedTypeNode(
            name: new Name('object'),
            fields: new FieldListNode($fields),
        );
    }

    /**
     * @param Field<InputFieldMetadata, mixed> $property
     */
    private function getField(Field $property, ExpandContext $context): NamedFieldNode|ScalarFieldNode
    {
        $name = $property->metadata->name;

        if ($this->isIdentifier($name)) {
            return new NamedFieldNode(
                key: new Identifier($name),
                type: $context->expand($property->declared),
                isOptional: $property->metadata->isOptional,
            );
        }

        return new ScalarFieldNode(
            key: new StringLiteralNode($name),
            type: $context->expand($property->declared),
            isOptional: $property->metadata->isOptional,
        );
    }

    /**
     * Whether the name is written as is, without quotes: words of letters,
     * digits and `_` joined by `-`, the first one not beginning in a digit.
     *
     * ```
     * object{city: string, user-name: string, 'first name': string}
     * ```
     */
    private function isIdentifier(string $name): bool
    {
        return \preg_match('/^[A-Za-z_\x80-\xFF][\w\x80-\xFF]*+(?:-[\w\x80-\xFF]++)*+$/', $name) === 1;
    }
}
