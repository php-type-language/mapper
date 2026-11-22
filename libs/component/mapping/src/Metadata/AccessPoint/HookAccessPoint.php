<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\AccessPoint;

/**
 * Hook of a PHP property of the direction: the `get` hook on
 * normalization, the `set` hook on denormalization (PHP 8.4+).
 */
final class HookAccessPoint implements AccessPointInterface
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
