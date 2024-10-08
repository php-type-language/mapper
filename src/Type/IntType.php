<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Attribute\TargetTemplateArgument;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Identifier;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class IntType implements TypeInterface
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_TYPE_NAME = 'int';

    protected readonly int $min;
    protected readonly int $max;

    /**
     * @param non-empty-string $name
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        #[TargetTypeName]
        protected readonly string $name = self::DEFAULT_TYPE_NAME,
        #[TargetTemplateArgument(allowedIdentifiers: ['min', 'max'])]
        int|Identifier $min = \PHP_INT_MIN,
        #[TargetTemplateArgument(allowedIdentifiers: ['min', 'max'])]
        int|Identifier $max = \PHP_INT_MAX,
    ) {
        $this->min = $this->formatIdentifier($min);
        $this->max = $this->formatIdentifier($max);

        if ($this->min > $this->max) {
            throw new \InvalidArgumentException(\sprintf(
                'Max value %d cannot be less than min %d',
                $this->max,
                $this->min,
            ));
        }
    }

    /**
     * Converts argument to its {@see int} value.
     *
     * @throws \InvalidArgumentException
     */
    private function formatIdentifier(int|Identifier $value): int
    {
        if (\is_int($value)) {
            return $value;
        }

        return match ($value->toString()) {
            'min' => \PHP_INT_MIN,
            'max' => \PHP_INT_MAX,
            default => throw new \InvalidArgumentException(\sprintf(
                'Invalid "%s" type identifier "%s"',
                $this->name,
                $value->toString(),
            )),
        };
    }

    private static function getExpectedArgument(int $value): TemplateArgumentNode
    {
        return new TemplateArgumentNode(
            value: match ($value) {
                \PHP_INT_MIN => new NamedTypeNode('min'),
                \PHP_INT_MAX => new NamedTypeNode('max'),
                default => new IntLiteralNode($value),
            },
        );
    }

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        if ($this->min === \PHP_INT_MIN && $this->max === \PHP_INT_MAX) {
            return new NamedTypeNode($this->name);
        }

        return new NamedTypeNode($this->name, arguments: new TemplateArgumentsListNode([
            self::getExpectedArgument($this->min),
            self::getExpectedArgument($this->max),
        ]));
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->tryCastToInt($value);
        }

        return \is_int($value) && $value >= $this->min && $value <= $this->max;
    }

    /**
     * Converts incoming value to the int (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): int
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->tryCastToInt($value);
        }

        if (!\is_int($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        if ($value > $this->max) {
            if ($context->isStrictTypesEnabled()) {
                throw InvalidValueException::becauseInvalidValueGiven(
                    value: $value,
                    expected: $this->getTypeStatement($context),
                    context: $context,
                );
            }

            $value = $this->max;
        }

        if ($value < $this->min) {
            if ($context->isStrictTypesEnabled()) {
                throw InvalidValueException::becauseInvalidValueGiven(
                    value: $value,
                    expected: $this->getTypeStatement($context),
                    context: $context,
                );
            }

            $value = $this->min;
        }

        return $value;
    }

    /**
     * A method to convert input data to a int representation, if possible.
     *
     * If conversion is not possible, it returns the value "as is".
     */
    protected function tryCastToInt(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        return match (true) {
            \is_array($value),
            \is_object($value) => $value,
            $value === \INF => \PHP_INT_MAX,
            $value === -\INF => \PHP_INT_MIN,
            \is_string($value) => match (true) {
                $value === '' => 0,
                $value[0] === '-' && \ctype_digit(\substr($value, 1)),
                \ctype_digit($value) => (int) $value,
                default => 1,
            },
            // @phpstan-ignore-next-line : Any other type can be converted to int
            default => (int) $value,
        };
    }
}
