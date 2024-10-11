<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class IntType extends NamedType
{
    public const DEFAULT_INT_MIN = \PHP_INT_MIN;
    public const DEFAULT_INT_MAX = \PHP_INT_MAX;

    /**
     * @var non-empty-string
     */
    public const DEFAULT_TYPE_NAME = 'int';

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        string $name = self::DEFAULT_TYPE_NAME,
        protected readonly int $min = self::DEFAULT_INT_MIN,
        protected readonly int $max = self::DEFAULT_INT_MAX,
        protected readonly bool $userDefinedRange = true,
    ) {
        parent::__construct($name);
    }

    private static function getExpectedArgument(int $value): TemplateArgumentNode
    {
        return new TemplateArgumentNode(
            value: match ($value) {
                self::DEFAULT_INT_MIN => new NamedTypeNode('min'),
                self::DEFAULT_INT_MAX => new NamedTypeNode('max'),
                default => new IntLiteralNode($value),
            },
        );
    }

    #[\Override]
    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        if ($this->userDefinedRange === false || !$context->isDetailedTypes()) {
            return parent::getTypeStatement($context);
        }

        if ($this->min === self::DEFAULT_INT_MIN && $this->max === self::DEFAULT_INT_MAX) {
            return parent::getTypeStatement($context);
        }

        return new NamedTypeNode($this->name, new TemplateArgumentsListNode([
            self::getExpectedArgument($this->min),
            self::getExpectedArgument($this->max),
        ]));
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        return \is_int($value)
            && $value >= $this->min
            && $value <= $this->max;
    }

    /**
     * Converts incoming value to the int (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): int
    {
        if (!\is_int($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        if ($value > $this->max) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        if ($value < $this->min) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        return $value;
    }
}
