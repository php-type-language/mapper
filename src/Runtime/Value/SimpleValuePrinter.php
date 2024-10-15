<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Value;

final class SimpleValuePrinter implements ValuePrinterInterface
{
    public function print(mixed $value, int $depth = 0): string
    {
        return \get_debug_type($value);
    }
}
