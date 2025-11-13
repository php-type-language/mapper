<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Exception\Definition\InternalTypeException;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TEnum of \BackedEnum = \BackedEnum
 * @template TResult of mixed = mixed
 * @template-extends Builder<NamedTypeNode, TypeInterface<TResult>>
 */
abstract class BackedEnumTypeBuilder extends Builder
{
    public function isSupported(TypeStatement $statement): bool
    {
        if (!$statement instanceof NamedTypeNode) {
            return false;
        }

        /** @var non-empty-string $enum */
        $enum = $statement->name->toString();

        return \enum_exists($statement->name->toString())
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

    public function build(TypeStatement $statement, BuildingContext $context): TypeInterface
    {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        $reflection = $this->createReflectionEnum($statement);

        $definition = $this->getBackedEnumType($reflection, $statement);

        return $this->create(
            /** @phpstan-ignore-next-line : The stmt name contains class-string<TEnum> */
            class: $statement->name->toString(),
            definition: $definition,
            /** @phpstan-ignore-next-line : The "getTypeByStatement" returns TypeInterface<value-of<TEnum>> */
            type: $context->getTypeByDefinition($definition),
        );
    }

    /**
     * @param class-string<TEnum> $class
     * @param non-empty-string $definition
     * @param TypeInterface<value-of<TEnum>> $type
     *
     * @return TypeInterface<TResult>
     */
    abstract protected function create(string $class, string $definition, TypeInterface $type): TypeInterface;

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
