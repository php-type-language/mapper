<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

interface ValueExceptionInterface extends RuntimeExceptionInterface
{
    /**
     * Returns the value that causes the error.
     *
     * @api
     */
    public function getValue(): mixed;
}
