<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\ConstMaskTypeBuilder;

enum ConstFinderMode
{
    /**
     * Find all constants with the specified prefix
     */
    case Prefix;

    /**
     * Find all constants with the specified suffix
     */
    case Suffix;

    /**
     * Find all constants with the specified occurrence
     */
    case Entrance;

    /**
     * Find all constants with the specified name
     */
    case Exact;
}
