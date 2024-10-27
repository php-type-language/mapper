<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

/**
 * Implements each driver that can supplement or modify existing
 * metadata with new property data.
 */
abstract class LoadableDriver extends Driver
{
    /**
     * @var array<class-string, ClassMetadata<object>>
     */
    private static array $metadata = [];

    public function __construct(
        DriverInterface $delegate = new NullDriver(),
    ) {
        parent::__construct($delegate);
    }

    /**
     * @template TArg of object
     *
     * @param \ReflectionClass<TArg> $class
     *
     * @return ClassMetadata<TArg>
     * @throws \Throwable in case of internal error occurred
     */
    public function getClassMetadata(
        \ReflectionClass $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ClassMetadata {
        if (isset(self::$metadata[$class->getName()])) {
            /** @var ClassMetadata<TArg> */
            return self::$metadata[$class->getName()];
        }

        self::$metadata[$class->getName()] = $metadata
            = parent::getClassMetadata($class, $types, $parser);

        $this->load($class, $metadata, $types, $parser);

        try {
            return $metadata;
        } finally {
            self::$metadata = [];
        }
    }

    /**
     * @template TArg of object
     *
     * @param \ReflectionClass<TArg> $reflection
     * @param ClassMetadata<TArg> $class
     *
     * @throws DefinitionException in case of type cannot be defined
     * @throws \Throwable in case of internal error occurred
     */
    abstract protected function load(
        \ReflectionClass $reflection,
        ClassMetadata $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void;
}
