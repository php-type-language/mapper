<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Otherwise;

/**
 * Type of the data with an unknown tag.
 */
class OtherwiseFallbackStub extends OtherwiseBaseStub
{
    public int $fallback = 2;
}
