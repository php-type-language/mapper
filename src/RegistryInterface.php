<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Exception\MapperExceptionInterface;

/**
 * @template T as object
 *
 * @psalm-suppress UndefinedAttributeClass
 */
interface RegistryInterface
{
    /**
     * Returns object of type {@see T} by the given non-empty string.
     *
     * @param non-empty-string $type
     *
     * @return T
     *
     * @throws MapperExceptionInterface May throws while type creating error.
     */
    public function get(#[Language('PHP')] string $type): object;
}
