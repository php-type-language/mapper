<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

/**
 * Class without attributes, its members are declared by a config.
 */
final class PlainAccessorStub
{
    private string $name = '';

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
