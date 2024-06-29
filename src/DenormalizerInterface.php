<?php

declare(strict_types=1);

namespace Serafim\Mapper;

interface DenormalizerInterface
{
    /**
     * @param non-empty-string $type
     */
    public function denormalize(mixed $value, string $type, ?Context $context = null): mixed;
}
