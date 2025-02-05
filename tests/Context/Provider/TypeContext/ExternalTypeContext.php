<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Provider\TypeContext;

use TypeLang\Mapper\Platform\Standard\Type\TypeInterface;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Tests\Context\Provider\TypeContext;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
abstract class ExternalTypeContext extends Context
{
    /**
     * @api
     */
    public function getCurrent(): TypeInterface
    {
        return $this->from(TypeContext::class)
            ->getCurrent();
    }

    /**
     * @api
     */
    public function setCurrent(TypeInterface $type): TypeInterface
    {
        return $this->from(TypeContext::class)
            ->setCurrent($type);
    }

    /**
     * @api
     */
    public function getStatement(): TypeStatement
    {
        return $this->from(TypeContext::class)
            ->getStatement();
    }

    /**
     * @api
     */
    public function setStatement(TypeStatement $type): TypeStatement
    {
        return $this->from(TypeContext::class)
            ->setStatement($type);
    }
}
