<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\InternalTypeException;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\BackedEnumType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends Builder<NamedTypeNode, BackedEnumType>
 */
class BackedEnumTypeBuilder extends Builder
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

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): BackedEnumType {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        $reflection = $this->createReflectionEnum($statement);

        $type = $this->getBackedEnumType($reflection, $statement);

        return new BackedEnumType(
            // @phpstan-ignore-next-line
            class: $statement->name->toString(),
            type: $types->getTypeByStatement(
                statement: $parser->getStatementByDefinition($type),
            ),
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
