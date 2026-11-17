<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\NotInterceptableExceptionInterface;

/**
 * Converts values of a type in both directions: data to a PHP value
 * (denormalization) and a PHP value to data (normalization). The nested
 * types of a composite type are given to it on building, the conversion
 * does not look them up.
 *
 * The directions may differ, such as an array and an object for a class.
 * Constraints of the type, such as `non-empty-string`, are checked on the
 * denormalization only.
 *
 * ```
 * $type->denormalize('42', $context);      // 42
 * $type->isDenormalizable('x', $context);  // false
 * $type->normalize(42, $context);          // 42
 * ```
 *
 * An {@see \Exception} is thrown in case of a value cannot be converted,
 * the composite types turn it into a violation with the path of the value.
 * An {@see \Error}, a fault of the program such as a {@see \TypeError} of
 * a property, and an error of the declaration pass through them as is.
 *
 * The checks `isX()` answer for the data and do not throw on it. An error
 * of the declaration, {@see NotInterceptableExceptionInterface} such as a
 * class the strategies reject, is thrown by the checks as by the
 * conversion.
 *
 * A built type is serializable together with its nested types, such as to
 * keep the built types in a cache.
 *
 * @template-covariant TValue value on the PHP side
 */
interface TypeInterface
{
    /**
     * Returns {@see true} if the data can be denormalized, without building
     * the result.
     *
     * @throws NotInterceptableExceptionInterface in case of the type is
     *         declared wrong
     */
    public function isDenormalizable(mixed $value, Context $context): bool;

    /**
     * @return TValue
     * @throws \Throwable in case of the data cannot be denormalized
     */
    public function denormalize(mixed $value, Context $context): mixed;

    /**
     * Returns {@see true} if the PHP value can be normalized, without
     * building the result.
     *
     * @throws NotInterceptableExceptionInterface in case of the type is
     *         declared wrong
     */
    public function isNormalizable(mixed $value, Context $context): bool;

    /**
     * Receives any PHP value, such as a property of an object, and checks
     * it is of the type.
     *
     * @throws \Throwable in case of the value cannot be normalized
     */
    public function normalize(mixed $value, Context $context): mixed;
}
