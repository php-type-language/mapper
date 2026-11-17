<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Tests\Stub;

use TypeLang\Mapper\Diagnostics\ViolationCodeInterface;

enum ExtensionViolationCodeStub: string implements ViolationCodeInterface
{
    case TooShort = 'too_short';

    public function getCode(): string
    {
        return $this->value;
    }

    public function getTemplate(): string
    {
        return 'Value must be at least {{min}} characters long, {{value}} given';
    }
}
