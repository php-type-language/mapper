<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Provider;

use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\ConfigurationInterface;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Tests\Extension\ContextArgumentTransformerExtension\AsTestingContext;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
#[AsTestingContext('config')]
final class ConfigurationContext extends Context
{
    private ?ConfigurationInterface $current = null;

    /**
     * @api
     */
    public function getCurrent(): ConfigurationInterface
    {
        return $this->current ??= $this->getDefault();
    }

    /**
     * @api
     */
    public function getDefault(): ConfigurationInterface
    {
        return new Configuration();
    }

    /**
     * @api
     */
    public function setCurrent(ConfigurationInterface $config): ConfigurationInterface
    {
        return $this->current = $config;
    }
}
