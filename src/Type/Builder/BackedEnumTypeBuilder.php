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
 * @template TResult of mixed = mixed
 *
 * @template-extends Builder<NamedTypeNode, TypeInterface<TResult>>
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
     * @param \ReflectionEnum<\BackedEnum> $reflection
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

    public function build(TypeStatement $stmt, BuildingContext $context): TypeInterface
    {
        $this->expectNoShapeFields($stmt);
        $this->expectNoTemplateArguments($stmt);

        $reflection = $this->createReflectionEnum($stmt);

        $definition = $this->getBackedEnumType($reflection, $stmt);

        return new BackedEnumType(
            /** @phpstan-ignore-next-line : The stmt name contains class-string<TEnum> */
            class: $stmt->name->toString(),
            /** @phpstan-ignore-next-line : The "getTypeByStatement" returns TypeInterface<value-of<TEnum>> */
            type: $context->getTypeByDefinition($definition),
        );
    }

    /**
     * @return \ReflectionEnum<\BackedEnum>
     * @throws InternalTypeException
     */
    protected function createReflectionEnum(NamedTypeNode $statement): \ReflectionEnum
    {
        try {
            /**
             * @var \ReflectionEnum<\BackedEnum> $reflection
             *
             * @phpstan-ignore-next-line
             */
            $reflection = new \ReflectionEnum($statement->name->toString());
        } catch (\ReflectionException $e) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $statement,
                message: 'The "{{type}}" must be an existing enum',
                previous: $e,
            );
        }

        if ($reflection->getCases() === []) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $statement,
                message: 'The "{{type}}" enum requires at least one case',
            );
        }

        return $reflection;
    }
}
