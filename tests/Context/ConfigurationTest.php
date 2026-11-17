<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context;

use TypeLang\Mapper\Context\Configuration;
use TypeLang\Mapper\Tests\TestCase;

final class ConfigurationTest extends TestCase
{
    public function testStrictnessIsOverriddenInCopy(): void
    {
        $config = new Configuration(isStrict: false);
        $strict = $config->withStrictTypes(true);

        self::assertNotSame($config, $strict);
        self::assertFalse($config->isStrict);
        self::assertTrue($strict->isStrict);
    }
}
