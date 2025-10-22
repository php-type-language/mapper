<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Extractor;

/**
 * Implements the basic logic for obtaining built-in PHP types by value.
 */
final class NativeTypeExtractor implements TypeExtractorInterface
{
    public function getDefinitionByValue(mixed $value): string
    {
        /** @var non-empty-string $result */
        $result = \get_debug_type($value);

        if ($result === 'class@anonymous') {
            return 'object';
        }

        return $result;
    }
}
