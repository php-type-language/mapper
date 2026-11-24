<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Otherwise;

/**
 * Not in the map, not an instance of the type of unknown tags.
 */
final class OtherwiseStrayStub extends OtherwiseBaseStub
{
    public int $stray = 3;
}
