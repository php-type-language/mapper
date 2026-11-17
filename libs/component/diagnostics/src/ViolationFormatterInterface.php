<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics;

/**
 * Renders the text of a violation without its path, such as a translated
 * one.
 */
interface ViolationFormatterInterface
{
    public function format(Violation $violation): string;
}
