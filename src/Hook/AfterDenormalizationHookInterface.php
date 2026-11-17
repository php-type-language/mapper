<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Hook;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * Runs after the object is created and filled from the data, such as to
 * check its invariants or to fill derived fields.
 *
 * The check of the data, such as {@see TypeInterface::isDenormalizable()},
 * creates no object and does not run the hook: an exception of the hook is
 * seen by the conversion only.
 */
interface AfterDenormalizationHookInterface extends ClassHookInterface
{
    /**
     * @param ClassMetadata<object> $metadata
     * @param mixed $data data the object is filled from
     */
    public function afterDenormalize(ClassMetadata $metadata, object $object, mixed $data, Context $context): void;
}
