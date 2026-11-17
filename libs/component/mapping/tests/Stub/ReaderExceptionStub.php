<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\Exception\ReaderException;

/**
 * Failure of the delegate stub.
 */
final class ReaderExceptionStub extends ReaderException
{
    public function __construct()
    {
        parent::__construct('Reading failed');
    }
}
