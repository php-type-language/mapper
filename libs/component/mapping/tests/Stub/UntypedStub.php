<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class UntypedStub
{
    // @phpstan-ignore-next-line the property is untyped on purpose
    public $untyped;

    public function getUntyped(): mixed
    {
        return $this->untyped;
    }
}
