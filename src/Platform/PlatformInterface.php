<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\TypeInterface;

interface PlatformInterface
{
    /**
     * Platform name to display anywhere.
     *
     * @return non-empty-string
     */
    public function getName(): string;

    /**
     * Returns a list of registered types for the specified platform.
     *
     * It is highly recommended to register a set of built-in PHP (1) types,
     * otherwise the mapper will not be able to determine these types when
     * automatically inferring (2) from values.
     *
     * 1. At least these types are expected 'null', 'bool', 'int', 'float',
     *    'string' and 'array'.
     * 2. Automatic type inference means inferring a type by value, for
     *    example in expressions like this:
     *    ```
     *    $mapper->denormalize(0xDEAD_BEEF);
     *
     *    // What does an expression like this mean
     *    $mapper->denormalize(0xDEAD_BEEF, 'int');
     *
     *    // In the case of the 'int' type is not registered,
     *    // the mapper will throw an exception.
     *    ```
     *
     * @return iterable<array-key, TypeBuilderInterface>
     */
    public function getTypes(DirectionInterface $direction): iterable;

    /**
     * Returns a list of registered type coercers for the specific platform.
     *
     * Type coercion is highly recommended if you're working in an environment
     * that handles undefined types. For example, when working with HTTP query
     * or body parameters, all values are {@see string}, so attempting to read
     * and map an {@see int} will result in a type incompatibility error.
     *
     * Type coercion objects contain rules that ensure such an external
     * {@see string} can be safely converted to an {@see int} number.
     *
     * ```
     * final readonly class QueryRequest
     * {
     *     public function __construct(
     *         public int $int,
     *         public float $float,
     *     ) {}
     * }
     *
     * // parsed result of "int=23&float=42.2" query string
     * $value = ['int' => '23', 'float' => '42.2'];
     *
     * $mapper->denormalize($value, QueryRequest::class);
     * ```
     *
     * In the example above, in case of {@see int} and {@see float} type
     * coercers are missing, then there will be a mapper type error.
     *
     * @return iterable<class-string<TypeInterface>, TypeCoercerInterface>
     */
    public function getTypeCoercers(DirectionInterface $direction): iterable;

    /**
     * Returns {@see true} in case of feature is supported.
     *
     * Each flag defines a set of language ({@link https://typelang.dev/basic-types.html})
     * constructs supported by the platform. For example, you can disable
     * literals, nullable types or anything else.
     */
    public function isFeatureSupported(GrammarFeature $feature): bool;
}
