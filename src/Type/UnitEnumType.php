<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;

/**
 * Case of an enum without values by its name. The name is denormalized by
 * the type of `string` first, a case is normalized to its name.
 *
 * @template T of \UnitEnum
 *
 * @template-implements TypeInterface<T>
 */
final class UnitEnumType implements TypeInterface
{
    /**
     * Cases by their names
     *
     * @var array<non-empty-string, T>
     */
    private readonly array $cases;

    public function __construct(
        /**
         * @var class-string<T>
         */
        public readonly string $class,
        /**
         * Type of the names
         *
         * @var TypeInterface<string>
         */
        public readonly TypeInterface $string,
    ) {
        $cases = [];

        foreach ($class::cases() as $case) {
            $cases[$case->name] = $case;
        }

        $this->cases = $cases;
    }

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        return $this->string->isDenormalizable($value, $context)
            && isset($this->cases[$this->string->denormalize($value, $context)]);
    }

    /**
     * @return T
     * @throws InvalidValueException in case of the value is not a name of a
     *         case
     * @throws \Throwable in case of the value is not a string
     */
    public function denormalize(mixed $value, Context $context): \UnitEnum
    {
        return $this->cases[$this->string->denormalize($value, $context)]
            ?? throw InvalidValueException::becauseInvalidType($value);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return $value instanceof $this->class;
    }

    /**
     * @throws InvalidValueException in case of the value is not a case of
     *         the enum
     */
    public function normalize(mixed $value, Context $context): string
    {
        if (!$value instanceof $this->class) {
            throw InvalidValueException::becauseInvalidType($value);
        }

        return $value->name;
    }
}
