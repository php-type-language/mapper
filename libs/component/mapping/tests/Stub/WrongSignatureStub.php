<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

/**
 * Methods that cannot be getters or setters.
 */
final class WrongSignatureStub
{
    #[MapGetter('a')]
    private function getA(): string
    {
        return '';
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
    public function resetD(): void {}

    #[MapSetter('e')]
    public function setE(string ...$tags): void {}
}
