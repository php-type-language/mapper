<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Platform\PlatformInterface;

abstract class Context
{
    protected function __construct(
        public readonly PlatformInterface $platform,
        /**
         * Gets current configuration.
         *
         * If you need to retrieve configuration's settings, it is recommended
         * to use the following methods:
         *
         * - {@see RuntimeContext::isObjectAsArray()}
         * - {@see RuntimeContext::isStrictTypesEnabled()}
         *
         * @readonly
         * @phpstan-readonly-allow-private-mutation
         */
        public Configuration $config,
    ) {}

    /**
     * A more convenient and correct way to get current "object as array"
     * configuration value.
     *
     * @see Configuration::isObjectAsArray()
     *
     * @link https://en.wikipedia.org/wiki/Law_of_Demeter
     */
    public function isObjectAsArray(): bool
    {
        return $this->config->isObjectAsArray();
    }

    /**
     * A more convenient and correct way to get current "strict types"
     * configuration value.
     *
     * @see Configuration::isStrictTypesEnabled()
     *
     * @link https://en.wikipedia.org/wiki/Law_of_Demeter
     */
    public function isStrictTypesEnabled(): bool
    {
        return $this->config->isStrictTypesEnabled();
    }
}
