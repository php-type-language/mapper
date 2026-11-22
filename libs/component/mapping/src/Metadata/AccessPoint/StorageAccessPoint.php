<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\AccessPoint;

/**
 * Backing store of a PHP property, its hooks are bypassed.
 */
final class StorageAccessPoint implements AccessPointInterface
{
    public function __construct(
        /**
         * Name of the PHP property
         *
         * @var non-empty-string
         */
        public readonly string $property,
    ) {}
}
