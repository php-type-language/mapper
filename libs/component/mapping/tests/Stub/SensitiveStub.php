<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\Sensitive;

final class SensitiveStub
{
    #[Sensitive]
    public string $password = '';

    #[Sensitive(false)]
    public string $login = '';

    public string $email = '';

    public function __construct(
        #[\SensitiveParameter]
        public string $token = '',
        public string $name = '',
    ) {}

    #[MapSetter('pin')]
    public function setPin(#[\SensitiveParameter] string $pin): void {}

    #[MapSetter('code')]
    public function setCode(string $code): void {}
}
