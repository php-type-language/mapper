<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use TypeLang\Mapper\Type\Context\Context;

interface NormalizerInterface
{
    /**
     * @param non-empty-string|null $type
     */
    public function normalize(mixed $value, ?string $type = null, ?Context $context = null): mixed;

    /**
     * @param non-empty-string|null $type
     */
    public function isNormalizable(mixed $value, ?string $type = null, ?Context $context = null): bool;
}
