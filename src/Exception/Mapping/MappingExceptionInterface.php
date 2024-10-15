<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Parser\Node\Stmt\TypeStatement;

interface MappingExceptionInterface extends RuntimeExceptionInterface
{
    /**
     * Returns the type statement in which the error occurred.
     *
     * @api
     */
    public function getExpectedType(): TypeStatement;
}
