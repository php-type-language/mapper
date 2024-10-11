<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Path;

interface PathPrinterInterface
{
    public function print(PathInterface $path): string;
}
