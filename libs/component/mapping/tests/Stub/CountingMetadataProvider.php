<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;

/**
 * Delegate creating new empty metadata on each call and counting the calls.
 */
final class CountingMetadataProvider implements MetadataProviderInterface
{
    /**
     * @var int<0, max>
     */
    public int $calls = 0;

    public function __construct(
        /**
         * Exception thrown on each call, {@see null} to provide the metadata
         */
        private readonly ?ReaderExceptionStub $exception = null,
    ) {}

    public function read(\ReflectionClass $class): ClassMetadata
    {
        ++$this->calls;

        if ($this->exception !== null) {
            throw $this->exception;
        }

        return new ClassMetadata($class->name);
    }
}
