<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Provider;

use Behat\Step\Given;
use TypeLang\Mapper\Platform\EmptyPlatform;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Tests\Extension\ContextArgumentTransformerExtension\AsTestingContext;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
#[AsTestingContext('platform')]
final class PlatformContext extends Context
{
    private ?PlatformInterface $current = null;

    /**
     * @api
     */
    public function getCurrent(): PlatformInterface
    {
        return $this->current ??= $this->getDefault();
    }

    /**
     * @api
     */
    public function getDefault(): PlatformInterface
    {
        $driver = $this->from(MetadataContext::class)
            ->getDriver();

        return new StandardPlatform($driver);
    }

    /**
     * @api
     */
    public function setCurrent(PlatformInterface $platform): PlatformInterface
    {
        return $this->current = $platform;
    }

    #[Given('/^platform "(?P<platform>[a-zA-Z0-9_\x80-\xff\\\\]+?)"$/')]
    public function givenPlatform(string $platform): void
    {
        // @phpstan-ignore-next-line
        $this->setCurrent(new $platform(
            $this->from(MetadataContext::class)
                ->getDriver(),
        ));
    }

    #[Given('no platform')]
    #[Given('empty platform')]
    public function givenEmptyPlatform(): void
    {
        $this->setCurrent(new EmptyPlatform());
    }

    #[Given('standard platform')]
    public function givenStandardPlatform(): void
    {
        $metadata = $this->from(MetadataContext::class);

        $this->setCurrent(new StandardPlatform(
            driver: $metadata->getDriver(),
        ));
    }
}
