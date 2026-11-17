<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Profiler;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;

/**
 * Reports each metadata read of a mapper to the collector, as the
 * decorator of the metadata provider of the mapper in the debug mode.
 */
final class TraceableMetadataProvider implements MetadataProviderInterface
{
    public function __construct(
        private readonly MetadataProviderInterface $delegate,
        /**
         * Name of the mapper in the profiler
         *
         * @var non-empty-string
         */
        private readonly string $name,
        private readonly MapperDataCollector $collector,
    ) {}

    public function read(\ReflectionClass $class): ClassMetadata
    {
        $startedAt = \hrtime(true);

        try {
            return $this->delegate->read($class);
        } finally {
            $this->collector->collectMetadata($this->name, $class->name, (\hrtime(true) - $startedAt) / 1e6);
        }
    }
}
