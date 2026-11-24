<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

/**
 * Level of the nested objects and arrays a normalization is in, shared by
 * the contexts of one mapper.
 */
final class Nesting
{
    /**
     * @var int<0, max>
     */
    public int $depth = 0;
}
