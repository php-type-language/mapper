<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Hook;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * Service run around the conversion of the objects of some classes, such
 * as with dependencies from a DI container. The hooks of a class are chosen
 * once, a class without hooks does not check for them on each object.
 *
 * A service implements the hooks of the points it needs:
 * {@see BeforeDenormalizationHookInterface},
 * {@see AfterDenormalizationHookInterface},
 * {@see BeforeNormalizationHookInterface},
 * {@see AfterNormalizationHookInterface}.
 */
interface ClassHookInterface
{
    /**
     * @param ClassMetadata<object> $metadata
     */
    public function isSupported(ClassMetadata $metadata): bool;
}
