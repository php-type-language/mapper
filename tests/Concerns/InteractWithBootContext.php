<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\BootContext;

trait InteractWithBootContext
{
    use InteractWithPlatform;
    use InteractWithConfiguration;

    protected static function createBootContext(?Configuration $config = null): BootContext
    {
        return BootContext::create(
            platform: self::getPlatform(),
            config: $config ?? self::getConfiguration(),
        );
    }
}
