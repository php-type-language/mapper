<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Hook;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * Runs before the object is created from the data, such as to migrate an
 * outdated payload.
 *
 * The hook is run by the check of the data too, such as
 * {@see TypeInterface::isDenormalizable()}, and may run more than once for
 * one value: it has no side effects and depends on its arguments only.
 */
interface BeforeDenormalizationHookInterface extends ClassHookInterface
{
    /**
     * Returns the data the object is created from.
     *
     * @param ClassMetadata<object> $metadata
     */
    public function beforeDenormalize(ClassMetadata $metadata, mixed $data, Context $context): mixed;
}
