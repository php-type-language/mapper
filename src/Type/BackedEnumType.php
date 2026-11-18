<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;

/**
 * Case of a backed enum by its value or an alias of the value. The value is
 * denormalized by the type of the backing values first, such as `'42'` to
 * `42` for an `int` enum unless the context is strict. A case is normalized
 * to its own value.
 *
 * @template T of \BackedEnum
 *
 * @template-implements TypeInterface<T>
 */
final class BackedEnumType implements TypeInterface
{
    /**
     * Cases by their values and by the aliases of the values
     *
     * @var array<array-key, T>
     */
    private readonly array $cases;

    public function __construct(
        /**
         * @var class-string<T>
         */
        public readonly string $class,
        /**
         * Type of the backing values, `int` or `string`
         *
         * @var TypeInterface<int|string>
         */
        public readonly TypeInterface $backing,
        /**
         * Values of the cases by the other values of the data denormalized
         * to them, such as the former values of the cases
         *
         * @var array<array-key, int|string>
         */
        public readonly array $aliases = [],
    ) {
        $cases = [];

        foreach ($class::cases() as $case) {
            $cases[$case->value] = $case;
        }

        foreach ($aliases as $alias => $target) {
            if (isset($cases[$target])) {
                $cases[$alias] = $cases[$target];
            }
        }

        $this->cases = $cases;
    }

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        if (!$this->backing->isDenormalizable($value, $context)) {
            return false;
        }

        return isset($this->cases[$this->backing->denormalize($value, $context)]);
    }

    /**
     * @return T
     * @throws InvalidValueException in case of the value is not a value or
     *         an alias of a case
     * @throws \Throwable in case of the value is not of the backing type
     */
    public function denormalize(mixed $value, Context $context): \BackedEnum
    {
        return $this->cases[$this->backing->denormalize($value, $context)]
            ?? throw InvalidValueException::becauseInvalidType($value);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return $value instanceof $this->class;
    }

    /**
     * Returns the backing value of the case.
     *
     * @throws InvalidValueException in case of the value is not a case of
     *         the enum
     */
    public function normalize(mixed $value, Context $context): int|string
    {
        if (!$value instanceof $this->class) {
            throw InvalidValueException::becauseInvalidType($value);
        }

        return $value->value;
    }
}
