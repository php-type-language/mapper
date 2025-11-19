<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnitEnumType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TEnum of \UnitEnum = \UnitEnum
 * @template-extends Builder<NamedTypeNode, TypeInterface<TEnum|non-empty-string>>
 */
class UnitEnumTypeBuilder extends Builder
{
    /**
     * @var non-empty-lowercase-string
     */
    public const DEFAULT_INNER_SCALAR_TYPE = 'string';

    public function __construct(
        /**
         * @var non-empty-string
         */
        protected readonly string $type = self::DEFAULT_INNER_SCALAR_TYPE,
    ) {}

    public function isSupported(TypeStatement $stmt): bool
    {
        if (!$stmt instanceof NamedTypeNode) {
            return false;
        }

        /** @var non-empty-string $enum */
        $enum = $stmt->name->toString();

        return \enum_exists($stmt->name->toString())
            && !\is_subclass_of($enum, \BackedEnum::class);
    }

    public function build(TypeStatement $stmt, BuildingContext $context): UnitEnumType
    {
        $this->expectNoShapeFields($stmt);
        $this->expectNoTemplateArguments($stmt);

        return new UnitEnumType(
            /** @phpstan-ignore-next-line : The stmt name contains class-string<TEnum> */
            class: $stmt->name->toString(),
            /** @phpstan-ignore-next-line : The "getTypeByStatement" returns TypeInterface<value-of<TEnum>> */
            type: $context->getTypeByDefinition($this->type),
        );
    }
}
