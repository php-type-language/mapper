<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Exception\MapperExceptionInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * An exception that occurs in case of errors during mapping process.
 */
interface MappingExceptionInterface extends MapperExceptionInterface
{
    /**
     * Path (name of fields and indexes) in the place where the error occurred.
     *
     * @return list<non-empty-string|int>
     */
    public function getPath(): array;

    /**
     * Expected type AST representation.
     */
    public function getExpectedType(): TypeStatement;
}
