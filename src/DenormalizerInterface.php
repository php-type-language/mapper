<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use TypeLang\Mapper\Type\Context\Context;

interface DenormalizerInterface
{
    /**
     * @param non-empty-string $type
     */
    public function denormalize(mixed $value, string $type, ?Context $context = null): mixed;

    /**
     * @param non-empty-string $type
     */
    public function isDenormalizable(mixed $value, string $type, ?Context $context = null): bool;
}
