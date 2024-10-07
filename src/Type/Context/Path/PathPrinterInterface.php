<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Context\Path;

interface PathPrinterInterface
{
    public function print(PathInterface $path): string;
}
