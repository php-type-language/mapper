<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Exception\MapperExceptionInterface;
use TypeLang\Mapper\Runtime\Path\PathInterface;

interface RuntimeExceptionInterface extends MapperExceptionInterface
{
    /**
     * Returns the path to the field where the error occurred.
     *
     * @api
     */
    public function getPath(): PathInterface;
}
