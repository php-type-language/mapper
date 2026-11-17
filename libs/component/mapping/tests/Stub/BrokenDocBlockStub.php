<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class BrokenDocBlockStub
{
    /**
     * @type-lang-var positive-int
     *
     * @broken-var <<<
     */
    public int $value = 1;
}
