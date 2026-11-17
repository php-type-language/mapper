<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Tests\Stub;

final class StaticStub
{
    public static string $publicStatic = 'public-static';

    protected static string $protectedStatic = 'protected-static';

    private static string $privateStatic = 'private-static';

    public string $instanceProperty = 'instance-value';
}
