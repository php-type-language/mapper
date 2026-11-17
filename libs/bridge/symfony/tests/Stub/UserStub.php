<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Stub;

use Symfony\Component\Validator\Constraints as Assert;

final class UserStub
{
    #[Assert\Length(min: 3)]
    public string $userName;

    public int $age;
}
