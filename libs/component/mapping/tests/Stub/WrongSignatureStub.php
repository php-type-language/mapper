<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

final class WrongSignatureStub
{
    public string $a = '';

    public string $b = '';

    public string $c = '';

    public string $d = '';

    #[MapGetter('a')]
    private function getA(): string
    {
        return $this->a;
    }

    #[MapGetter('b')]
    public static function getB(): string
    {
        return '';
    }

    #[MapGetter('c')]
    public function findC(string $key): string
    {
        return $key;
    }

    #[MapSetter('d')]
    public function resetD(): void
    {
        $this->d = '';
    }
}
