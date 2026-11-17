<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Printer\Path;

use TypeLang\Mapper\Diagnostics\Path\Path;

/**
 * Prints a path in a notation of its reader, such as JSONPath of the input
 * or a trace of classes for a debugger.
 */
interface PathPrinterInterface
{
    public function print(Path $path): string;
}
