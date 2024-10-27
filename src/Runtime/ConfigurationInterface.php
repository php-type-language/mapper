<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

use Psr\Log\LoggerInterface;

interface ConfigurationInterface
{
    /**
     * Returns current {@see $objectsAsArrays} option or default value
     * in case of option is not set.
     */
    public function isObjectsAsArrays(): bool;

    /**
     * Returns current {@see $detailedTypes} option or default value
     * in case of option is not set.
     */
    public function isDetailedTypes(): bool;

    /**
     * Returns the currently used logger.
     */
    public function getLogger(): ?LoggerInterface;
}
