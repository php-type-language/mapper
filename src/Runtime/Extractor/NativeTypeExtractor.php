<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Extractor;

final class NativeTypeExtractor implements TypeExtractorInterface
{
    public function getDefinitionByValue(mixed $value): string
    {
        /** @var non-empty-string */
        return \get_debug_type($value);
    }
}
