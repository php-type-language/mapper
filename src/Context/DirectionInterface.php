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
     * Returns {@see true} in case of the mapping direction allows the use
     * of safe unchecked types, otherwise, by default we try them to cast
     */
    public function isSafeTypes(): bool;

    /**
     * Returns {@see true} in case of allowed displaying PHP internal type
     * information. Otherwise, all internal types should be replaced with
     * generalized analogs
     */
    public function isPublicTypes(): bool;

    /**
     * Returns {@see true} in case of mapping direction
     * from PHP outwards.
     */
    public function isOutput(): bool;
}
