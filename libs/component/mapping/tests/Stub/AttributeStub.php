<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub as Account;

final class AttributeStub
{
    #[MapName('user_id')]
    public int $id = 0;

    /**
     * @var list<Account>
     */
    #[MapType('list<Account>')]
    public array $accounts = [];

    #[MapType('int', true)]
    public int|string $value = 0;

    /**
     * @broken-var <<<
     */
    #[MapType('positive-int')]
    public int $count = 1;
}
