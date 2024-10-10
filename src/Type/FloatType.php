<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

class FloatType extends SimpleType
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_TYPE_NAME = 'float';

    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name = self::DEFAULT_TYPE_NAME)
    {
        parent::__construct($name);
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->tryCastToFloat($value);
        }

        return \is_float($value) || \is_int($value);
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): float
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->tryCastToFloat($value);
        }

        if (!\is_float($value) && !\is_int($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: new UnionTypeNode(
                    a: new NamedTypeNode('int'),
                    b: new NamedTypeNode('float'),
                ),
                context: $context,
            );
        }

        return (float) $value;
    }

    /**
     * A method to convert input data to a float representation, if possible.
     *
     * If conversion is not possible, it returns the value "as is".
     */
    protected function tryCastToFloat(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        return match (true) {
            \is_array($value),
            \is_object($value) => $value,
            \is_string($value) => match (true) {
                $value === '' => 0.0,
                \is_numeric($value) => (float) $value,
                default => 1.0,
            },
            // @phpstan-ignore-next-line : Any other type can be converted to float
            default => (float) $value,
        };
    }
}
