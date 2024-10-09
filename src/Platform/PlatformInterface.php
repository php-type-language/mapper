<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

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
     * @return iterable<array-key, TypeBuilderInterface<covariant TypeStatement, TypeInterface>>
     */
    public function getTypes(): iterable;

    /**
     * Returns {@see true} in case of feature is supported.
     *
     * Each flag defines a set of language ({@link https://typelang.dev/basic-types.html})
     * constructs supported by the platform. For example, you can disable
     * literals, nullable types or anything else.
     */
    public function isFeatureSupported(GrammarFeature $feature): bool;
}
