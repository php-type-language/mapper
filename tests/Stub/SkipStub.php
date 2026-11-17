<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\SkipWhen;
use TypeLang\Mapper\Mapping\SkipWhenEmpty;
use TypeLang\Mapper\Mapping\SkipWhenNull;

final class SkipStub
{
    #[SkipWhenNull]
    public ?string $nickname = null;

    /**
     * @var list<string>
     */
    #[SkipWhenEmpty]
    public array $tags = [];

    #[SkipWhen('this.isPrivate')]
    public string $email = '';

    public bool $isPrivate = false;
}
