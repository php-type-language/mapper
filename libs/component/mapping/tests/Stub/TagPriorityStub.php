<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class TagPriorityStub
{
    /**
     * @var array
     * @type-lang-var list<int>
     */
    public array $special = [];

    /**
     * @var array
     * @api-var list<string>
     */
    public array $custom = [];
}
