<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Mapping\Driver\AttributeDriver;
use TypeLang\Mapper\Mapping\Driver\DriverInterface;
use TypeLang\Mapper\Mapping\Driver\InMemoryCachedDriver;
use TypeLang\Mapper\Mapping\Driver\ReflectionDriver;

abstract class Platform implements PlatformInterface
{
    protected readonly DriverInterface $driver;

    public function __construct(?DriverInterface $driver = null)
    {
        // We can store all classes in RAM by force, since their
        // number is limited. This will not lead to memory leaks.
        $this->driver = new InMemoryCachedDriver(
            delegate: $driver ?? $this->createDefaultMetadataDriver(),
        );
    }

    protected function createDefaultMetadataDriver(): DriverInterface
    {
        return new AttributeDriver(
            delegate: new ReflectionDriver(),
        );
    }
}
