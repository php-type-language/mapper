<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

interface ConfigurationInterface
{
    /**
     * Returns current {@see $objectsAsArrays} option or default value
     * in case of option is not set.
     *
     * @api
     */
    public function isObjectsAsArrays(): bool;

    /**
     * Returns current {@see $detailedTypes} option or default value
     * in case of option is not set.
     *
     * @api
     */
    public function isDetailedTypes(): bool;
}
