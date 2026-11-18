<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics;

/**
 * Kind of a violation whose template names the expected type with the
 * `{{expected}}` placeholder.
 */
interface TypedViolationCodeInterface extends ViolationCodeInterface
{
    /**
     * Returns the text for a violation whose expected type is unknown, such
     * as hidden from the reader, without the `{{expected}}` placeholder.
     */
    public function getUntypedTemplate(): string;
}
