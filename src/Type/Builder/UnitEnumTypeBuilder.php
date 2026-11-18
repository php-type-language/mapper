<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnitEnumType;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of an enum without values, its names are denormalized by
 * the type of `string` from the repository.
 *
 * @template-implements TypeBuilderInterface<\UnitEnum>
 */
final class UnitEnumTypeBuilder implements TypeBuilderInterface
{
    /**
     * @return UnitEnumType<\UnitEnum>|null
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?UnitEnumType
    {
        if (!$node instanceof NamedTypeNode || $node->arguments !== null || $node->fields !== null) {
            return null;
        }

        $name = $node->name->toClassString();

        if ($name === null || !\enum_exists($name) || \is_a($name, \BackedEnum::class, true)) {
            return null;
        }

        /** @var TypeInterface<string> */
        $string = $context->types->getType(new NamedTypeNode(new Name('string')));

        return new UnitEnumType($name, $string);
    }
}
