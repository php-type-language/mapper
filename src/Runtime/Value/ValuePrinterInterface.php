<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Value;

interface ValuePrinterInterface
{
    public function print(mixed $value): string;
}
