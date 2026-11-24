<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class PlainSecretChildStub extends PlainSecretStub
{
    public static string $total = 'child';

    private string $own = 'own';

    public function getOwn(): string
    {
        return $this->own;
    }
}
