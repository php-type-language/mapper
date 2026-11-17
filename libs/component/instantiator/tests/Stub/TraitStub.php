<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Tests\Stub;

// @phpstan-ignore-next-line the trait is never used, it is passed by name only
trait TraitStub
{
    public static function create(): object
    {
        return new \stdClass();
    }
}
