<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Extractor;

/**
 * Responsible for obtaining the type declarations.
 */
interface TypeExtractorInterface
{
    /**
     * Returns the type declaration (type name) given its value.
     *
     * For example:
     *
     * ```
     * $extractor->getDefinitionByValue(42); // "int"
     * $extractor->getDefinitionByValue(true); // "bool"
     * $extractor->getDefinitionByValue(.2); // "float"
     * ```
     *
     * @return non-empty-string
     * @throws \Throwable in case of any internal error occurs
     */
    public function getDefinitionByValue(mixed $value): string;
}
