<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Mapper\Exception\Mapping\RuntimeException;

interface NormalizerInterface
{
    /**
     * Normalizes a specific value to generic data.
     *
     * Can be used to convert to structural data (for JSON, BSON, MessagePack, etc)
     * from an PHP values.
     *
     * In case that the type is specified as {@see null}, it is automatically
     * inferred from the passed value.
     *
     * ```
     * $normalizer->normalize(new ExampleClass(
     *     id: 42,
     *     name: 'Kirill',
     * ));
     * ```
     *
     * @param non-empty-string|null $type
     *
     * @throws RuntimeException in case of runtime mapping exception occurs
     * @throws DefinitionException in case of type building exception occurs
     * @throws \Throwable in case of any internal error occurs
     */
    public function normalize(mixed $value, ?string $type = null): mixed;

    /**
     * Returns {@see true} if the value can be normalized for the given type.
     *
     * In case that the type is specified as {@see null}, it is automatically
     * inferred from the passed value.
     *
     * @param non-empty-string|null $type
     *
     * @throws DefinitionException in case of type building exception occurs
     * @throws \Throwable in case of any internal error occurs
     */
    public function isNormalizable(mixed $value, ?string $type = null): bool;
}
