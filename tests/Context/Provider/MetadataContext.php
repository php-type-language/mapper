<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Provider;

use Behat\Step\Given;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver;
use TypeLang\Mapper\Mapping\Driver\DriverInterface;
use TypeLang\Mapper\Mapping\Driver\NullDriver;
use TypeLang\Mapper\Mapping\Driver\ReflectionDriver;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Tests\Extension\ContextArgumentTransformerExtension\AsTestingContext;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
#[AsTestingContext('meta')]
final class MetadataContext extends Context
{
    private ?DriverInterface $driver = null;

    /**
     * @api
     */
    public function getDriver(): ?DriverInterface
    {
        return $this->driver;
    }

    /**
     * @api
     */
    public function setDriver(DriverInterface $driver): DriverInterface
    {
        return $this->driver = $driver;
    }

    #[Given('no metadata driver')]
    public function givenNullDriver(): void
    {
        $this->setDriver(new NullDriver());
    }

    #[Given('reflection metadata driver')]
    public function givenReflectionDriver(): void
    {
        $this->setDriver(new ReflectionDriver());
    }

    #[Given('attribute metadata driver')]
    public function givenAttributesDriver(): void
    {
        $this->setDriver(new AttributeDriver());
    }
}
