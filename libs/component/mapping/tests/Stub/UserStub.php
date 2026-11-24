<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class UserStub
{
    public static int $counter = 0;

    public int $id;

    public ?string $email = null;

    public ?self $friend = null;

    public ?string $displayName = null;

    protected string $secret = '';

    // @phpstan-ignore-next-line the property is only read by reflection
    private string $hidden = '';

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
}
