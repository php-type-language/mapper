<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapSetter;

/**
 * Setter recording its calls, next to a property of another type.
 */
final class SideEffectStub
{
    /**
     * @var list<int>
     */
    public static array $calls = [];

    public int $b = 0;

    #[MapSetter('a')]
    public function setA(int $a): void
    {
        self::$calls[] = $a;
    }
}
