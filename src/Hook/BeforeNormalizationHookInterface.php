<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Hook;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * Runs before the object is turned into data, such as to load a lazy
 * object.
 *
 * The hook is run by the check of the object too, such as
 * {@see TypeInterface::isNormalizable()}, and may run more than once for
 * one object: it has no side effects visible in the data.
 */
interface BeforeNormalizationHookInterface extends ClassHookInterface
{
    /**
     * Returns the object of the class turned into data.
     *
     * @template TArgObject of object
     * @param ClassMetadata<TArgObject> $metadata
     * @param TArgObject $object
     * @return TArgObject
     */
    public function beforeNormalize(ClassMetadata $metadata, object $object, Context $context): object;
}
