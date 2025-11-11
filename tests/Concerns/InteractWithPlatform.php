<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

use PHPUnit\Framework\Attributes\Before;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;

trait InteractWithPlatform
{
    protected static ?PlatformInterface $currentPlatform = null;

    #[Before]
    public function beforeInteractWithPlatform(): void
    {
        self::$currentPlatform = null;
    }

    protected static function withPlatform(PlatformInterface $platform): void
    {
        self::$currentPlatform = $platform;
    }

    private static function createPlatform(): PlatformInterface
    {
        return new StandardPlatform();
    }

    protected static function getPlatform(): PlatformInterface
    {
        return self::$currentPlatform ??= self::createPlatform();
    }
}
