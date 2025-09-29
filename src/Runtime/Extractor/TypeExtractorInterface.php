<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Extractor;

interface TypeExtractorInterface
{
    /**
     * @return non-empty-string
     * @throws \Throwable in case of any internal error occurs
     */
    public function getDefinitionByValue(mixed $value): string;
}
