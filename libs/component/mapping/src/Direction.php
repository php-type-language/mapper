<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * Direction of the mapping a property takes part in.
 */
enum Direction: string
{
    /**
     * Object to data, the property is read
     */
    case Normalization = 'normalization';

    /**
     * Data to object, the property is written
     */
    case Denormalization = 'denormalization';
}
