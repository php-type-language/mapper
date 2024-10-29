<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Provider;

use TypeLang\Mapper\Runtime\Parser\TypeParser;
use TypeLang\Mapper\Runtime\Parser\TypeParserFacade;
use TypeLang\Mapper\Runtime\Parser\TypeParserFacadeInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Tests\Extension\ContextArgumentTransformerExtension\AsTestingContext;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
#[AsTestingContext('parser')]
final class TypeParserContext extends Context
{
    private ?TypeParserInterface $current = null;

    private ?TypeParserFacadeInterface $facade = null;

    /**
     * @api
     */
    public function getCurrent(): TypeParserInterface
    {
        return $this->current ??= $this->getDefault();
    }

    /**
     * @api
     */
    public function getFacade(): TypeParserFacadeInterface
    {
        return $this->facade ??= new TypeParserFacade($this->getCurrent());
    }

    /**
     * @api
     */
    public function getDefault(): TypeParserInterface
    {
        $platform = $this->from(PlatformContext::class)
            ->getCurrent();

        return TypeParser::createFromPlatform($platform);
    }

    /**
     * @api
     */
    public function setCurrent(TypeParserInterface $parser): TypeParserInterface
    {
        $this->facade = null;
        return $this->current = $parser;
    }
}
