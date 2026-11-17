<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Hook;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * Runs after the object is turned into data, such as to add links.
 */
interface AfterNormalizationHookInterface extends ClassHookInterface
{
    /**
     * Returns the data of the object.
     *
     * @param ClassMetadata<object> $metadata
     * @param array<array-key, mixed> $data
     * @return array<array-key, mixed>
     */
    public function afterNormalize(ClassMetadata $metadata, object $object, array $data, Context $context): array;
}
