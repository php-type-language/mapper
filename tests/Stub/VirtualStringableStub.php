<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

final class VirtualStringableStub
{
    private string $stored = '';

    public string $id {
        get => $this->stored;
        set(string|\Stringable $value) {
            $this->stored = (string) $value;
        }
    }
}
