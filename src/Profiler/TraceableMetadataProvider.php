<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Profiler;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\EnumMetadata;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;

/**
 * Reports each metadata read of a mapper to the profiler.
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
        private readonly ProfilerInterface $profiler,
    ) {}

    public function read(\ReflectionClass $class): ClassMetadata
    {
        $startedAt = \hrtime(true);

        try {
            return $this->delegate->read($class);
        } finally {
            $this->profiler->recordMetadataRead($this->name, $class->name, (\hrtime(true) - $startedAt) / 1e6);
        }
    }

    public function readEnum(\ReflectionEnum $enum): EnumMetadata
    {
        $startedAt = \hrtime(true);

        try {
            return $this->delegate->readEnum($enum);
        } finally {
            $this->profiler->recordMetadataRead($this->name, $enum->name, (\hrtime(true) - $startedAt) / 1e6);
        }
    }
}
