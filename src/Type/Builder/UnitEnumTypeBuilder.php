<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Exception\Definition\InternalTypeException;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TEnum of \UnitEnum = \UnitEnum
 * @template TResult of mixed = mixed
 * @template-extends Builder<NamedTypeNode, TypeInterface<TResult>>
 */
abstract class UnitEnumTypeBuilder extends Builder
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

    public function build(TypeStatement $stmt, BuildingContext $context): TypeInterface
    {
        $this->expectNoShapeFields($stmt);
        $this->expectNoTemplateArguments($stmt);

        $names = \iterator_to_array($this->getEnumCaseNames($stmt), false);

        if ($names === []) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $stmt,
                message: 'The "{{type}}" enum requires at least one case',
            );
        }

        return $this->create(
            // @phpstan-ignore-next-line
            class: $stmt->name->toString(),
            cases: $names,
            type: $context->getTypeByDefinition(
                definition: $this->type,
            ),
        );
    }

    /**
     * @param class-string<TEnum> $class
     * @param non-empty-list<non-empty-string> $cases
     * @param TypeInterface<string> $type
     *
     * @return TypeInterface<TResult>
     */
    abstract protected function create(string $class, array $cases, TypeInterface $type): TypeInterface;

    /**
     * @return \Traversable<array-key, non-empty-string>
     */
    private function getEnumCaseNames(NamedTypeNode $statement): \Traversable
    {
        /** @var class-string<\UnitEnum> $enum */
        $enum = $statement->name->toString();

        foreach ($enum::cases() as $case) {
            // @phpstan-ignore-next-line : Enum case name cannot be empty
            yield $case->name;
        }
    }
}
