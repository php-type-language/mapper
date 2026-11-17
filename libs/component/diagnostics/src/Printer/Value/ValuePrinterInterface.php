<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Printer\Value;

/**
 * Prints a rejected value for a particular audience. The same value is
 * shown differently to a developer, with full details, and to a public
 * user, without internal data and arguments marked with
 * `#[\SensitiveParameter]`.
 */
interface ValuePrinterInterface
{
    /**
     * Never calls user code of the value.
     */
    public function print(mixed $value): string;
}
