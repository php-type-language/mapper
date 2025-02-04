<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Mapper\Exception\Mapping\RuntimeException;

interface DenormalizerInterface
{
    /**
     * Denormalizes a specific type from generic data.
     *
     * Can be used to convert to PHP values from an external data source
     * such as JSON, MessagePack, BSON, etc.
     *
     * ```
     * $denormalizer->denormalize(\json_decode('{
     *     "id": 42,
     *     "name": "Kirill"
     * }'), ExampleClass::class);
     * ```
     *
     * @param non-empty-string $type
     *
     * @throws RuntimeException in case of runtime mapping exception occurs
     * @throws DefinitionException in case of type building exception occurs
     * @throws \Throwable in case of any internal error occurs
     */
    public function denormalize(mixed $value, string $type): mixed;

    /**
     * Returns {@see true} if the value can be denormalized for the given type.
     *
     * @param non-empty-string $type
     *
     * @throws DefinitionException in case of type building exception occurs
     * @throws \Throwable in case of any internal error occurs
     */
    public function isDenormalizable(mixed $value, string $type): bool;
}
