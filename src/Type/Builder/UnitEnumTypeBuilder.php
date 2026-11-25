<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnitEnumType;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of an enum without values with the aliases of its
 * metadata, its names are denormalized by the type of `string` from the
 * repository.
 *
 * @template-implements TypeBuilderInterface<\UnitEnum>
 */
final class UnitEnumTypeBuilder implements TypeBuilderInterface
{
    /**
     * @return UnitEnumType<\UnitEnum>|null
     * @throws TypeDefinitionException in case of a nested type cannot be built
     * @throws InvalidMetadataException in case of the metadata has wrong
     *         values
     * @throws ReaderException in case of the metadata cannot be provided
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

        $aliases = $context->metadata->readEnum(new \ReflectionEnum($name))->aliases;

        return new UnitEnumType($name, $string, $aliases);
    }
}
