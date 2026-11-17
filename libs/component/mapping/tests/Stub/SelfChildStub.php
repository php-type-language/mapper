<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class SelfChildStub extends SelfBaseStub
{
    /**
     * @var parent|null
     */
    public ?object $parent = null;
}
