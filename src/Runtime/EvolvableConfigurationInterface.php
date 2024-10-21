<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

interface EvolvableConfigurationInterface
{
    /**
     * Enables or disables object to arrays conversion.
     *
     * In case of $enabled is {@see null} a default value will be defined.
     *
     * @api
     */
    public function withObjectsAsArrays(?bool $enabled = null): self;

    /**
     * Enables or disables detailed types in exceptions.
     *
     * In case of $enabled is {@see null} a default value will be defined.
     *
     * @api
     */
    public function withDetailedTypes(?bool $enabled = null): self;
}
