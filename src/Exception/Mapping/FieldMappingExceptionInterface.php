<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

/**
 * @deprecated TODO
 */
interface FieldMappingExceptionInterface extends MappingExceptionInterface
{
    /**
     * @return non-empty-string
     */
    public function getFieldName(): string;
}
