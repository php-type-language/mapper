<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Exception\Definition\InternalTypeException;
use TypeLang\Mapper\Type\BackedEnumType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TEnum of \BackedEnum = \BackedEnum
 * @template-extends Builder<NamedTypeNode, TypeInterface<TEnum|int|string>>
 */
class BackedEnumTypeBuilder extends Builder
{
    public function isSupported(TypeStatement $stmt): bool
    {
        if (!$stmt instanceof NamedTypeNode) {
            return false;
        }

        /** @var non-empty-string $enum */
        $enum = $stmt->name->toString();

        return \enum_exists($stmt->name->toString())
            && \is_subclass_of($enum, \BackedEnum::class);
    }

    /**
     * @param \ReflectionEnum<TEnum> $reflection
     *
     * @return non-empty-string
     * @throws InternalTypeException
     */
    private function getBackedEnumType(\ReflectionEnum $reflection, NamedTypeNode $statement): string
    {
        $type = $reflection->getBackingType();

        if (!$type instanceof \ReflectionNamedType) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $statement,
                message: 'The "{{type}}" must provide type (a subtype of BackedEnum)',
            );
        }

        /** @var non-empty-string */
        return $type->getName();
    }

    public function build(TypeStatement $stmt, BuildingContext $context): BackedEnumType
    {
        $this->expectNoShapeFields($stmt);
        $this->expectNoTemplateArguments($stmt);

        /** @var class-string<TEnum> $class */
        $class = $stmt->name->toString();

        $reflection = $this->createReflectionEnum($class, $stmt);
        $definition = $this->getBackedEnumType($reflection, $stmt);

        return new BackedEnumType(
            class: $class,
            /** @phpstan-ignore-next-line : The "getTypeByStatement" returns TypeInterface<value-of<TEnum>> */
            type: $context->getTypeByDefinition($definition),
        );
    }

    /**
     * @param class-string<TEnum> $class
     * @return \ReflectionEnum<TEnum>
     * @throws InternalTypeException
     */
    protected function createReflectionEnum(string $class, NamedTypeNode $stmt): \ReflectionEnum
    {
        try {
            $reflection = new \ReflectionEnum($class);
        } catch (\ReflectionException $e) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $stmt,
                message: 'The "{{type}}" must be an existing enum',
                previous: $e,
            );
        }

        if ($reflection->getCases() === []) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $stmt,
                message: 'The "{{type}}" enum requires at least one case',
            );
        }

        return $reflection;
    }
}
