<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Naming;

/**
 * Computes the external key of a property from its name.
 */
interface NamingStrategyInterface
{
    /**
     * Returns the key for the given property name.
     *
     * @param non-empty-string $name
     * @return non-empty-string
     */
    public function rename(string $name): string;
}
