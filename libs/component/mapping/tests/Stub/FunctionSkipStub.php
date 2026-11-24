<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\SkipWhen;

final class FunctionSkipStub
{
    #[SkipWhen('constant("PHP_INT_SIZE") > 1')]
    public string $email = '';

    #[SkipWhen('this.isPrivate(constant("PHP_INT_SIZE"))')]
    public string $phone = '';

    #[SkipWhen('this.isPrivate(1) and this.isPublic')]
    public string $name = '';

    public bool $isPublic = true;

    public function isPrivate(int $level): bool
    {
        return $level > 0;
    }
}
