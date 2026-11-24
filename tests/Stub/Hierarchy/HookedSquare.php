<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Hierarchy;

use TypeLang\Mapper\Mapping\MapGetter;

/**
 * Class outside the map with its own access to the property of its parent.
 */
final class HookedSquare extends Square
{
    public int $side = 2 {
        #[MapGetter]
        get => $this->side * 10;
    }
}
