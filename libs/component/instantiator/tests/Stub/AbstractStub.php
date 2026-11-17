<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Tests\Stub;

abstract class AbstractStub
{
    public static function create(): self
    {
        return new class extends AbstractStub {
            public static function createAbstract(): AbstractStub
            {
                return new self();
            }
        };
    }

    abstract public static function createAbstract(): self;
}
