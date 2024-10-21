<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Context;

interface DirectionInterface
{
    /**
     * Returns {@see true} in case of current direction works
     * towards normalization.
     */
    public function isNormalization(): bool;

    /**
     * Returns {@see true} in case of current direction works
     * towards denormalization.
     */
    public function isDenormalization(): bool;
}
