<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Path;

use TypeLang\Mapper\Runtime\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Runtime\Path\Entry\ObjectPropertyEntry;

final class JsonPathPrinter implements PathPrinterInterface
{
    public function print(PathInterface $path): string
    {
        $result = '$';

        foreach ($path as $entry) {
            $result .= match (true) {
                $entry instanceof ArrayIndexEntry => \is_numeric($entry->value) ? "[$entry]" : ".$entry",
                $entry instanceof ObjectPropertyEntry => ".$entry",
                default => '',
            };
        }

        return $result;
    }
}
