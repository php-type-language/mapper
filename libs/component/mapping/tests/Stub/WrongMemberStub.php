<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

/**
 * Members that cannot be accessed: a static property, a getter returning
 * nothing, the constructor.
 */
final class WrongMemberStub
{
    #[MapGetter]
    public static int $shared = 0;

    #[MapSetter('value')]
    public function __construct(int $value = 0) {}

    #[MapGetter('nothing')]
    public function touch(): void {}
}
