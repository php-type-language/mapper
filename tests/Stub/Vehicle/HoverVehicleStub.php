<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Vehicle;

/**
 * Child of the root that is not in its map and has no mapped parent.
 */
final class HoverVehicleStub extends VehicleStub
{
    public int $height = 1;
}
