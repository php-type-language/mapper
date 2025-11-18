<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Extractor;

/**
 * Implements the basic logic for obtaining built-in PHP types by value.
 */
final class NativeTypeExtractor implements TypeExtractorInterface
{
    public function getDefinitionByValue(mixed $value): string
    {
        if (\is_resource($value)) {
            return 'resource';
        }

        /** @var non-empty-string $result */
        $result = \get_debug_type($value);

        return match ($result) {
            'class@anonymous' => 'object',
            'resource (closed)' => 'resource',
            default => $result,
        };
    }
}
