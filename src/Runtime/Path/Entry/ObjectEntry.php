<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Path\Entry;

final class ObjectEntry extends Entry
{
    /**
     * @param class-string $class
     */
    public function __construct(string $class)
    {
        parent::__construct($class);
    }
}
