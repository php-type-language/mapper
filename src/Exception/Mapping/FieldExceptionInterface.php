<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

interface FieldExceptionInterface extends RuntimeExceptionInterface
{
    /**
     * Returns object field name where the error occurred.
     *
     * @return non-empty-string
     */
    public function getField(): string;
}
