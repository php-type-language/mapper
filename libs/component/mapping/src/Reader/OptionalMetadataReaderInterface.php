<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

/**
 * Reader depending on an optional package or PHP extension.
 */
interface OptionalMetadataReaderInterface extends MetadataReaderInterface
{
    /**
     * Returns {@see true} in case of the dependencies of the reader are
     * installed and the reader can be created.
     */
    public static function isAvailable(): bool;
}
