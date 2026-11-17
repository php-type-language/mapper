<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapType;

final class MethodAttributeStub
{
    #[MapType('positive-int')]
    public int $id = 1;

    #[MapType('positive-int')]
    public function getId(): int
    {
        return $this->id;
    }
}
