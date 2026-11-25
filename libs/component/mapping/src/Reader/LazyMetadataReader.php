<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;

/**
 * Reader created on its first read.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping
 */
final class LazyMetadataReader implements MetadataReaderInterface
{
    private ?MetadataReaderInterface $reader = null;

    public function __construct(
        /**
         * @var \Closure(): MetadataReaderInterface
         */
        private readonly \Closure $factory,
    ) {}

    public function read(\ReflectionClass $class, ?ClassDraft $metadata = null): ClassDraft
    {
        $this->reader ??= ($this->factory)();

        return $this->reader->read($class, $metadata);
    }
}
