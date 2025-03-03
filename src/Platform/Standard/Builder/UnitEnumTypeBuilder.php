<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Builder;

use TypeLang\Mapper\Exception\Definition\InternalTypeException;
use TypeLang\Mapper\Platform\Standard\Type\UnitEnumType;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends Builder<NamedTypeNode, UnitEnumType>
 */
class UnitEnumTypeBuilder extends Builder
{
    /**
     * @var non-empty-lowercase-string
     */
    public const DEFAULT_INNER_SCALAR_TYPE = 'string';

    /**
     * @param non-empty-string $type
     */
    public function __construct(
        protected readonly string $type = self::DEFAULT_INNER_SCALAR_TYPE,
    ) {}

    public function isSupported(TypeStatement $statement): bool
    {
        if (!$statement instanceof NamedTypeNode) {
            return false;
        }

        /** @var non-empty-string $enum */
        $enum = $statement->name->toString();

        return \enum_exists($statement->name->toString())
            && !\is_subclass_of($enum, \BackedEnum::class);
    }

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): UnitEnumType {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        $names = \iterator_to_array($this->getEnumCaseNames($statement), false);

        if ($names === []) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $statement,
                message: 'The "{{type}}" enum requires at least one case',
            );
        }

        return new UnitEnumType(
            // @phpstan-ignore-next-line
            class: $statement->name->toString(),
            cases: $names,
            type: $types->getTypeByStatement(
                statement: $parser->getStatementByDefinition(
                    definition: $this->type,
                ),
            ),
        );
    }

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
