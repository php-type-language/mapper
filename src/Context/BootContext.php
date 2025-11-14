<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Platform\PlatformInterface;

final class BootContext extends Context
{
    public static function create(PlatformInterface $platform, Configuration $config): self
    {
        return new self($platform, $config);
    }
}
