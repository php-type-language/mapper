<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @deprecated TODO
 */
interface ValueMappingExceptionInterface extends MappingExceptionInterface
{
    /**
     * Actual type AST representation where the error occurred.
     */
    public function getActualType(): TypeStatement;

    /**
     * Actual passed value where the error occurred.
     */
    public function getActualValue(): mixed;
}
