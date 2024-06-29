<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

interface FieldMappingExceptionInterface extends MappingExceptionInterface
{
    /**
     * @return non-empty-string
     */
    public function getFieldName(): string;
}
