<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Sensitive;

final class SensitiveStub
{
    #[Sensitive]
    public string $password = '';

    /**
     * @var array<string, string>
     */
    #[Sensitive]
    #[MapType('array<string, string>')]
    public array $secrets = [];

    public string $login = '';

    public function __construct(
        #[\SensitiveParameter]
        public string $token = '',
    ) {}
}
