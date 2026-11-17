<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

final class UserStub
{
    public int $id;

    /**
     * @var non-empty-string
     */
    public string $name;

    public string $role = 'user';

    public ?AddressStub $address;

    /**
     * @var int|string
     */
    public mixed $code;
}
