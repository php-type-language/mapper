<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Type\Check\CheckInterface;

/**
 * Value of the decorated type satisfying all checks, such as
 * `non-empty-string` over `string`. The checks apply to denormalization
 * only, a value is normalized by the decorated type as is.
 *
 * ```
 * $type = new RefinedType(
 *     type: new StringType(),
 *     checks: [new NonEmptyCheck()],
 * );
 *
 * $type->denormalize('', $context); // InvalidValueException
 * $type->normalize('', $context);   // ''
 * ```
 *
 * @template T
 *
 * @template-implements TypeInterface<T>
 */
final class RefinedType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<T>
         */
        public readonly TypeInterface $type,
        /**
         * @var non-empty-list<CheckInterface>
         */
        public readonly array $checks,
    ) {}

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        if (!$this->type->isDenormalizable($value, $context)) {
            return false;
        }

        $result = $this->type->denormalize($value, $context);

        foreach ($this->checks as $check) {
            if (!$check->isValid($result)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws InvalidValueException in case of a check fails
     * @throws \Throwable in case of the decorated type cannot denormalize
     *         the value
     */
    public function denormalize(mixed $value, Context $context): mixed
    {
        $result = $this->type->denormalize($value, $context);

        foreach ($this->checks as $check) {
            if (!$check->isValid($result)) {
                throw InvalidValueException::becauseCheckFailed($check->description, $value);
            }
        }

        return $result;
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return $this->type->isNormalizable($value, $context);
    }

    public function normalize(mixed $value, Context $context): mixed
    {
        return $this->type->normalize($value, $context);
    }
}
