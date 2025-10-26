<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Extractor;

/**
 * Responsible for obtaining the type declaration from its value.
 *
 * For example:
 * ```
 * $extractor->getDefinitionByValue(42); // "int"
 * $extractor->getDefinitionByValue(true); // "bool"
 * $extractor->getDefinitionByValue(.2); // "float"
 * ```
 */
interface TypeExtractorInterface
{
    /**
     * @return non-empty-string
     * @throws \Throwable in case of any internal error occurs
     */
    public function getDefinitionByValue(mixed $value): string;
}
