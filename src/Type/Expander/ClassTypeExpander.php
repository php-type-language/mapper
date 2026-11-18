<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Expander;

use TypeLang\Mapper\Context\ExpandContext;
use TypeLang\Mapper\Type\ClassType;
use TypeLang\Mapper\Type\ClassType\Property;
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
 * by their external names, a field with a default value is optional.
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
     * @param Property<mixed> $property
     */
    private function getField(Property $property, ExpandContext $context): NamedFieldNode|ScalarFieldNode
    {
        $name = $property->metadata->externalName;

        if ($this->isIdentifier($name)) {
            return new NamedFieldNode(
                key: new Identifier($name),
                type: $context->expand($property->declared),
                isOptional: $property->metadata->default !== null,
            );
        }

        return new ScalarFieldNode(
            key: new StringLiteralNode($name),
            type: $context->expand($property->declared),
            isOptional: $property->metadata->default !== null,
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
