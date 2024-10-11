<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

interface NormalizerInterface
{
    /**
     * @param non-empty-string|null $type
     */
    public function normalize(mixed $value, ?string $type = null): mixed;

    /**
     * @param non-empty-string|null $type
     */
    public function isNormalizable(mixed $value, ?string $type = null): bool;
}
