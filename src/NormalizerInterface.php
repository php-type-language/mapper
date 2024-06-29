<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

interface NormalizerInterface
{
    /**
     * @param non-empty-string|null $type
     */
    public function normalize(mixed $value, ?string $type = null, ?Context $context = null): mixed;
}
