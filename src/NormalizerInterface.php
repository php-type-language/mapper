<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\TypeDefinitionException;

/**
 * Converts a PHP value of the type to data, such as for JSON. A value
 * without the type is converted by `mixed`: an object by the type of its
 * class, an array element by element.
 *
 * ```
 * $json = \json_encode($normalizer->normalize($user));
 * ```
 */
interface NormalizerInterface
{
    /**
     * @param non-empty-string|null $type
     * @throws InvalidValueException in case of the value does not match the
     *         type, the message has the path of the wrong value
     * @throws TypeDefinitionException in case of the type is not supported,
     *         written or declared wrong
     */
    public function normalize(mixed $value, #[Language('PHP')] ?string $type = null): mixed;

    /**
     * @param non-empty-string|null $type
     * @throws TypeDefinitionException in case of the type is not supported,
     *         written or declared wrong
     */
    public function isNormalizable(mixed $value, #[Language('PHP')] ?string $type = null): bool;
}
