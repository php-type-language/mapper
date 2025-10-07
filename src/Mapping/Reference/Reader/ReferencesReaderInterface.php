<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reference\Reader;

interface ReferencesReaderInterface
{
    /**
     * @param \ReflectionClass<object> $class
     *
     * @return array<int|non-empty-string, non-empty-string>
     */
    public function getUseStatements(\ReflectionClass $class): array;
}
