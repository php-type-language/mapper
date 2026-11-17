<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\TypeDefinitionException;

/**
 * Converts data, such as decoded JSON, to a PHP value of the type.
 *
 * ```
 * $user = $denormalizer->denormalize(\json_decode($json, true), User::class);
 * ```
 */
interface DenormalizerInterface
{
    /**
     * @template TArgObject of object
     * @param class-string<TArgObject>|non-empty-string $type
     * @return ($type is class-string<TArgObject> ? TArgObject : mixed)
     * @throws InvalidValueException in case of the data does not match the
     *         type, the message has the path of the wrong value
     * @throws TypeDefinitionException in case of the type is not supported,
     *         written or declared wrong
     */
    public function denormalize(mixed $value, #[Language('PHP')] string $type): mixed;

    /**
     * @param non-empty-string $type
     * @throws TypeDefinitionException in case of the type is not supported,
     *         written or declared wrong
     */
    public function isDenormalizable(mixed $value, #[Language('PHP')] string $type): bool;
}
