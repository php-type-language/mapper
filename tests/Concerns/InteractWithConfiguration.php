<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

use PHPUnit\Framework\Attributes\Before;
use TypeLang\Mapper\Configuration;

trait InteractWithConfiguration
{
    protected static ?Configuration $currentConfiguration = null;

    #[Before]
    public function beforeInteractWithConfiguration(): void
    {
        self::$currentConfiguration = null;
    }

    protected static function withConfiguration(Configuration $config): void
    {
        self::$currentConfiguration = $config;
    }

    private static function createConfiguration(): Configuration
    {
        return new Configuration();
    }

    protected static function getConfiguration(): Configuration
    {
        return self::$currentConfiguration ??= self::createConfiguration();
    }
}
