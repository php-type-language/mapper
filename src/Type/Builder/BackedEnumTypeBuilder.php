<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\BackedEnumType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of a backed enum, its backing values are denormalized by
 * the type of `int` or `string` from the repository, the aliases of the
 * values are taken from the metadata of the enum.
 *
 * @template-implements TypeBuilderInterface<\BackedEnum>
 */
final class BackedEnumTypeBuilder implements TypeBuilderInterface
{
    /**
     * @return BackedEnumType<\BackedEnum>|null
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?BackedEnumType
    {
        if (!$node instanceof NamedTypeNode || $node->arguments !== null || $node->fields !== null) {
            return null;
        }

        $name = $node->name->toClassString();

        if ($name === null || !\is_a($name, \BackedEnum::class, true)) {
            return null;
        }

        $enum = new \ReflectionEnum($name);

        /** @var 'int'|'string' */
        $backing = (string) $enum->getBackingType();

        /** @var TypeInterface<int|string> */
        $type = $context->types->getType(new NamedTypeNode(new Name($backing)));

        $aliases = $context->metadata->readEnum($enum)->aliases;

        return new BackedEnumType($name, $type, $aliases);
    }
}
