<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

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
    #[SkipWhen('owner.email === ""', context: 'owner')]
    public string $email = '';

    public bool $isPrivate = false;
}
