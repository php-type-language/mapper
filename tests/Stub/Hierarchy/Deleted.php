<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Hierarchy;

class Deleted implements Event
{
    public string $reason = 'gone';
}
