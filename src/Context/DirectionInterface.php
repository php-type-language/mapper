<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

interface DirectionInterface
{
    /**
     * Returns name of a direction
     *
     * @return non-empty-string
     */
    public function getName(): string;

    /**
     * Returns {@see true} in case of mapping direction
     * from PHP outwards.
     */
    public function isOutput(): bool;
}
