<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\SkipCondition;

/**
 * Condition the property is left out of the normalized data under, stored
 * in the metadata ready to be evaluated.
 */
interface SkipConditionInterface
{
    /**
     * @param object $object object the property is read from
     * @param mixed $value value of the property
     */
    public function isMatched(object $object, mixed $value): bool;
}
