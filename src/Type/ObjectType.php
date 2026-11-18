<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;

/**
 * `object`: denormalized into a {@see \stdClass} by its type, an object is
 * normalized by the type of its class, as `mixed` does.
 *
 * @template-implements TypeInterface<object>
 */
final class ObjectType implements TypeInterface
{
    public function __construct(
        /**
         * Type of {@see \stdClass} the data is denormalized by
         *
         * @var TypeInterface<array<array-key, mixed>|\stdClass>
         */
        public readonly TypeInterface $input,
        /**
         * Type selecting the type of object by its class
         *
         * @var TypeInterface<mixed>
         */
        public readonly TypeInterface $output,
    ) {}

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        return $this->input->isDenormalizable($value, $context);
    }

    /**
     * @throws InvalidValueException in case of the value is not an array or
     *         a {@see \stdClass}
     */
    public function denormalize(mixed $value, Context $context): object
    {
        $result = $this->input->denormalize($value, $context);

        \assert($result instanceof \stdClass);

        return $result;
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return \is_object($value) && $this->output->isNormalizable($value, $context);
    }

    /**
     * @throws InvalidValueException in case of the value is not an object or
     *         cannot be normalized by the type of its class
     */
    public function normalize(mixed $value, Context $context): mixed
    {
        if (!\is_object($value)) {
            throw InvalidValueException::becauseInvalidType($value);
        }

        return $this->output->normalize($value, $context);
    }
}
