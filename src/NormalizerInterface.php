<?php

declare(strict_types=1);

namespace Serafim\Mapper;

interface NormalizerInterface
{
    public function normalize(mixed $value, ?string $type = null, ?Context $context = null): mixed;
}
