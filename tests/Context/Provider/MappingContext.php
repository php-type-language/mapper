<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Provider;

use Behat\Step\When;
use PHPUnit\Framework\Assert;
use TypeLang\Mapper\Runtime\Context\Direction;
use TypeLang\Mapper\Runtime\Context\DirectionInterface;
use TypeLang\Mapper\Runtime\Context\RootContext;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Runtime\Context as RuntimeContext;
use TypeLang\Mapper\Tests\Extension\ContextArgumentTransformerExtension\AsTestingContext;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
#[AsTestingContext('mapping')]
final class MappingContext extends Context
{
    private ?RuntimeContext $context = null;

    private ?DirectionInterface $direction = null;

    /**
     * @api
     */
    public function getDirection(): DirectionInterface
    {
        Assert::assertNotNull($this->direction, 'Mapping direction is not set');

        return $this->direction;
    }

    /**
     * @api
     */
    public function setDirection(DirectionInterface $direction): DirectionInterface
    {
        return $this->direction = $direction;
    }

    /**
     * @api
     */
    public function getContext(): RuntimeContext
    {
        Assert::assertNotNull($this->context, 'Mapping context is not set');

        return $this->context;
    }

    /**
     * @api
     */
    public function setContext(RuntimeContext $context): RuntimeContext
    {
        return $this->context = $context;
    }

    /**
     * @api
     */
    public function setContextByValue(mixed $value): RuntimeContext
    {
        $direction = $this->getDirection();

        if ($direction->isNormalization()) {
            return $this->setContext(RootContext::forNormalization(
                value: $value,
                config: $this->from(ConfigurationContext::class)
                    ->getCurrent(),
                parser: $this->from(TypeParserContext::class)
                    ->getFacade(),
                types: $this->from(TypeRepositoryContext::class)
                    ->getFacade(),
            ));
        }

        return $this->setContext(RootContext::forDenormalization(
            value: $value,
            config: $this->from(ConfigurationContext::class)
                ->getCurrent(),
            parser: $this->from(TypeParserContext::class)
                ->getFacade(),
            types: $this->from(TypeRepositoryContext::class)
                ->getFacade(),
        ));
    }

    #[When('normalize')]
    public function whenNormalization(): void
    {
        $this->setDirection(Direction::Normalize);
    }

    #[When('denormalize')]
    public function whenDenormalize(): void
    {
        $this->setDirection(Direction::Denormalize);
    }
}
