<?php

declare(strict_types=1);

namespace Serafim\Mapper\Type;

use Serafim\Mapper\Context\LocalContext;
use Serafim\Mapper\Exception\Mapping\InvalidValueException;
use Serafim\Mapper\Exception\StringInfo;
use Serafim\Mapper\Registry\RegistryInterface;
use Serafim\Mapper\Type\Attribute\TargetTemplateArgument;
use Serafim\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Parser\Node\Identifier;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\ArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\ArgumentsListNode;

/**
 * @template-extends NonDirectionalType<int, int>
 */
final class IntType extends NonDirectionalType
{
    /**
     * @var non-empty-string
     */
    private const DEFAULT_TYPE_NAME = 'int';

    private readonly int $min;
    private readonly int $max;

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        #[TargetTypeName]
        private readonly string $name = self::DEFAULT_TYPE_NAME,
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
                'Invalid %s type identifier %s',
                StringInfo::quoted($this->name),
                StringInfo::quoted($value->toString()),
            )),
        };
    }

    private static function getExpectedArgument(int $value): ArgumentNode
    {
        return new ArgumentNode(
            value: match ($value) {
                \PHP_INT_MIN => new NamedTypeNode('min'),
                \PHP_INT_MAX => new NamedTypeNode('max'),
                default => new IntLiteralNode($value),
            },
        );
    }

    private function getExpectedTypeStatement(): NamedTypeNode
    {
        if ($this->min === \PHP_INT_MIN && $this->max === \PHP_INT_MAX) {
            return new NamedTypeNode($this->name);
        }

        return new NamedTypeNode($this->name, arguments: new ArgumentsListNode([
            self::getExpectedArgument($this->min),
            self::getExpectedArgument($this->max),
        ]));
    }

    /**
     * Converts incoming value to the int (in case of strict types is disabled).
     */
    protected function format(mixed $value, RegistryInterface $types, LocalContext $context): int
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->castToIntIfPossible($value);
        }

        if (!\is_int($value)) {
            throw InvalidValueException::becauseInvalidValue(
                context: $context,
                expectedType: $this->getExpectedTypeStatement(),
                actualValue: $value,
            );
        }

        if ($value > $this->max) {
            if ($context->isStrictTypesEnabled()) {
                throw InvalidValueException::becauseInvalidValue(
                    context: $context,
                    expectedType: $this->getExpectedTypeStatement(),
                    actualValue: $value,
                );
            }

            $value = $this->max;
        }

        if ($value < $this->min) {
            if ($context->isStrictTypesEnabled()) {
                throw InvalidValueException::becauseInvalidValue(
                    context: $context,
                    expectedType: $this->getExpectedTypeStatement(),
                    actualValue: $value,
                );
            }

            $value = $this->min;
        }

        return (int) $value;
    }

    /**
     * A method to convert input data to a int representation, if possible.
     *
     * If conversion is not possible, it returns the value "as is".
     */
    private function castToIntIfPossible(mixed $value): mixed
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
            default => (int) $value,
        };
    }
}
