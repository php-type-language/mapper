<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;

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
     */
    public function normalize(mixed $value, #[Language('PHP')] ?string $type = null): mixed;

    /**
     * Returns {@see true} if the value can be normalized for the given type.
     *
     * In case that the type is specified as {@see null}, it is automatically
     * inferred from the passed value.
     *
     * @param non-empty-string|null $type
     * @throws TypeNotFoundException in case of type not found
     * @throws \Throwable in case of any internal error occurs
     */
    public function isNormalizable(mixed $value, #[Language('PHP')] ?string $type = null): bool;
}
