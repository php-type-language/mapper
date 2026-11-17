<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

class SelfBaseStub
{
    /**
     * @var self|null
     */
    public ?object $self = null;

    /**
     * @var static|null
     */
    public ?object $static = null;
}
