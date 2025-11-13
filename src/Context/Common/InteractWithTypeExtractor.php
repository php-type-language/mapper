<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context\Common;

use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;

/**
 * @phpstan-require-implements TypeExtractorInterface
 */
trait InteractWithTypeExtractor
{
    /**
     * Responsible for obtaining the type declaration from its value.
     *
     * This extractor belongs to the current context and may differ from the
     * initial (mappers) one.
     *
     * You can safely use all the methods of this interface, but for ease of
     * use, the following methods are available to you:
     *
     * - {@see RuntimeContext::getDefinitionByValue()} - returns definition string
     *   by the passed value.
     */
    public readonly TypeExtractorInterface $extractor;

    public function getDefinitionByValue(mixed $value): string
    {
        return $this->extractor->getDefinitionByValue($value);
    }
}
