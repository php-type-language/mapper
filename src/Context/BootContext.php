<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Platform\PlatformInterface;

/**
 * Contains minimal information to launch and initialize internal services
 */
final class BootContext extends Context
{
    public static function create(PlatformInterface $platform, Configuration $config): self
    {
        return new self($platform, $config);
    }
}
