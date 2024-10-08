<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Path;

use TypeLang\Mapper\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Path\Entry\ObjectPropertyEntry;

final class JsonPathPrinter implements PathPrinterInterface
{
    public function print(PathInterface $path): string
    {
        $result = '$';

        foreach ($path as $entry) {
            $result .= match (true) {
                $entry instanceof ArrayIndexEntry => "[$entry]",
                $entry instanceof ObjectPropertyEntry => ".$entry",
                default => '',
            };
        }

        return $result;
    }
}
