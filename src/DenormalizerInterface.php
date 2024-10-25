<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;

interface DenormalizerInterface
{
    /**
     * @param non-empty-string $type
     */
    public function denormalize(mixed $value, #[Language('PHP')] string $type): mixed;

    /**
     * @param non-empty-string $type
     */
    public function isDenormalizable(mixed $value, #[Language('PHP')] string $type): bool;
}
