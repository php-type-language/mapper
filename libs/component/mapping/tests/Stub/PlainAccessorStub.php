<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class PlainAccessorStub
{
    public string $name = '';

    public string $createdAt = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(int|string $name): void
    {
        $this->name = (string) $name;
    }
}
