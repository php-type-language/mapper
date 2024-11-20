<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;

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
     */
    public function denormalize(mixed $value, #[Language('PHP')] string $type): mixed;

    /**
     * Returns {@see true} if the value can be denormalized for the given type.
     *
     * @param non-empty-string $type
     * @throws TypeNotFoundException in case of type not found
     * @throws \Throwable in case of any internal error occurs
     */
    public function isDenormalizable(mixed $value, #[Language('PHP')] string $type): bool;
}
