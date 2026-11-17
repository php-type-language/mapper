<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Attribute;

/**
 * Attribute adding an autoconfigured service to the extensions of one kind
 * of mappers, such as the type builders.
 */
abstract class MapperExtensionAttribute
{
    public function __construct(
        /**
         * Names of the mappers the service extends, the default mapper and
         * all named ones inheriting it in case of an empty list
         *
         * @var list<non-empty-string>
         */
        public readonly array $mappers = [],
        /**
         * Place among the services of the same kind and mapper, a higher
         * one goes first
         */
        public readonly int $priority = 0,
    ) {}
}
