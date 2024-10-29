<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Provider;

use PHPUnit\Framework\Assert;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryFacade;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryFacadeInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Tests\Extension\ContextArgumentTransformerExtension\AsTestingContext;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
#[AsTestingContext('types')]
final class TypeRepositoryContext extends Context
{
    private ?TypeRepositoryInterface $current = null;

    private ?TypeRepositoryFacadeInterface $facade = null;

    /**
     * @api
     */
    public function getCurrent(): TypeRepositoryInterface
    {
        return $this->current ??= $this->getDefault();
    }

    /**
     * @api
     */
    public function getFacade(): TypeRepositoryFacadeInterface
    {
        return $this->facade ??= new TypeRepositoryFacade(
            parser: $this->from(TypeParserContext::class)
                ->getFacade(),
            runtime: $this->getCurrent(),
        );
    }

    /**
     * @api
     */
    public function getDefault(): TypeRepositoryInterface
    {
        return TypeRepository::createFromPlatform(
            platform: $this->from(PlatformContext::class)
                ->getCurrent(),
            parser: $this->from(TypeParserContext::class)
                ->getCurrent(),
        );
    }

    /**
     * @api
     */
    public function setCurrent(TypeRepositoryInterface $types): TypeRepositoryInterface
    {
        $this->facade = null;
        return $this->current = $types;
    }
}
