<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\InvalidTemplateArgumentException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Type\Coercer\IntTypeCoercer;
use TypeLang\Mapper\Type\Coercer\StringTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\Specifier\AllOfSpecifier;
use TypeLang\Mapper\Type\Specifier\IntGreaterThanOrEqualSpecifier;
use TypeLang\Mapper\Type\Specifier\IntRangeSpecifier;
use TypeLang\Mapper\Type\Specifier\TypeSpecifierInterface;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode as ArgNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

/**
 * @template-extends NamedTypeBuilder<IntType>
 */
class IntRangeTypeBuilder extends NamedTypeBuilder
{
    public function __construct(
        array|string $name,
        /**
         * @var TypeCoercerInterface<int>
         */
        protected readonly TypeCoercerInterface $coercer,
        /**
         * @var TypeSpecifierInterface<int>|null
         */
        protected readonly ?TypeSpecifierInterface $specifier = null,
    ) {
        parent::__construct($name);
    }

    /**
     * @throws InvalidTemplateArgumentException
     * @throws TemplateArgumentHintsNotSupportedException
     * @throws TooManyTemplateArgumentsException
     * @throws ShapeFieldsNotSupportedException
     */
    #[\Override]
    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): IntType {
        /** @phpstan-ignore-next-line : Additional DbC assertion */
        assert($statement instanceof NamedTypeNode);

        $this->expectNoShapeFields($statement);

        $arguments = $statement->arguments->items ?? [];

        return match (\count($arguments)) {
            0 => new IntType(),
            1 => $this->buildWithMinValue($statement, $arguments[0]),
            2 => $this->buildWithMinMaxValues($statement, $arguments[0], $arguments[1]),
            default => throw TooManyTemplateArgumentsException::becauseTemplateArgumentsRangeOverflows(
                minSupportedArgumentsCount: 0,
                maxSupportedArgumentsCount: 2,
                type: $statement,
            ),
        };
    }

    /**
     * @throws InvalidTemplateArgumentException
     * @throws TemplateArgumentHintsNotSupportedException
     */
    private function buildWithMinValue(NamedTypeNode $statement, ArgNode $min): IntType
    {
        $specifier = new IntGreaterThanOrEqualSpecifier(
            expected: $this->fetchTemplateArgumentValue($statement, $min),
        );

        if ($this->specifier !== null) {
            $specifier = new AllOfSpecifier([$specifier, $this->specifier]);
        }

        return new IntType($this->coercer, $specifier);
    }

    /**
     * @throws InvalidTemplateArgumentException
     * @throws TemplateArgumentHintsNotSupportedException
     */
    private function buildWithMinMaxValues(NamedTypeNode $statement, ArgNode $min, ArgNode $max): IntType
    {
        $specifier = new IntRangeSpecifier(
            min: $this->fetchTemplateArgumentValue($statement, $min),
            max: $this->fetchTemplateArgumentValue($statement, $max),
        );

        if ($this->specifier !== null) {
            $specifier = new AllOfSpecifier([$specifier, $this->specifier]);
        }

        return new IntType($this->coercer, $specifier);
    }

    /**
     * @throws InvalidTemplateArgumentException
     * @throws TemplateArgumentHintsNotSupportedException
     */
    private function fetchTemplateArgumentValue(TypeStatement $statement, ArgNode $argument): int
    {
        $this->expectNoTemplateArgumentHint($statement, $argument);

        $value = $argument->value;

        if ($value instanceof IntLiteralNode) {
            return $value->value;
        }

        if ($value instanceof NamedTypeNode) {
            switch ($value->name->toLowerString()) {
                case 'min':
                    return IntRangeSpecifier::DEFAULT_MIN_VALUE;
                case 'max':
                    return IntRangeSpecifier::DEFAULT_MAX_VALUE;
            }
        }

        throw InvalidTemplateArgumentException::becauseTemplateArgumentIsInvalid(
            expected: new UnionTypeNode(
                new NamedTypeNode('int'),
                StringLiteralNode::createFromValue('min'),
                StringLiteralNode::createFromValue('max'),
            ),
            argument: $argument,
            type: $statement,
        );
    }
}
