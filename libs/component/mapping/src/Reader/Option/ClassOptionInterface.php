<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;

/**
 * One option of a class read from its attributes and from its config, such
 * as the discriminator. A wrong value is an error added to the collector,
 * the class is returned without it.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
interface ClassOptionInterface
{
    /**
     * Reads the attributes of the class, of its methods or of its
     * constants.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     */
    public function readClassAttributes(
        \ReflectionClass $class,
        ClassDraft $metadata,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): ClassDraft;

    /**
     * Returns the keys of a class config the option reads.
     *
     * @return list<non-empty-string>
     */
    public function getClassKeys(): array;

    /**
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     */
    public function readClassConfig(
        \ReflectionClass $class,
        ConfigSection $config,
        ClassDraft $metadata,
        ErrorCollector $errors,
    ): ClassDraft;
}
