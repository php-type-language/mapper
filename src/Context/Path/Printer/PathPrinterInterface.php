<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context\Path\Printer;

use TypeLang\Mapper\Context\Path\PathInterface;

interface PathPrinterInterface
{
    public function print(PathInterface $path): string;
}
