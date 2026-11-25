<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Printer\Value;

/**
 * Prints a rejected value for a particular audience. The same value is
 * shown differently to a developer, with the classes of the objects, and
 * to a reader outside of the application, without them. Scalars are shown
 * by value to both.
 */
interface ValuePrinterInterface
{
    /**
     * Never calls user code of the value.
     */
    public function print(mixed $value): string;
}
