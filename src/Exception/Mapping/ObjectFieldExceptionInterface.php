<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Parser\Node\Stmt\TypeStatement;

interface ObjectFieldExceptionInterface extends FieldExceptionInterface
{
    /**
     * Returns actual type where field has been defined.
     *
     * @api
     */
    public function getExpectedObject(): TypeStatement;
}
