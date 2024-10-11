<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

interface DenormalizerInterface
{
    /**
     * @param non-empty-string $type
     */
    public function denormalize(mixed $value, string $type): mixed;

    /**
     * @param non-empty-string $type
     */
    public function isDenormalizable(mixed $value, string $type): bool;
}
