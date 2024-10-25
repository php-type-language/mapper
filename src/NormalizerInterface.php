<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;

interface NormalizerInterface
{
    /**
     * @param non-empty-string|null $type
     */
    public function normalize(mixed $value, #[Language('PHP')] ?string $type = null): mixed;

    /**
     * @param non-empty-string|null $type
     */
    public function isNormalizable(mixed $value, #[Language('PHP')] ?string $type = null): bool;
}
