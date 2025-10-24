<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context\Path\Printer;

use TypeLang\Mapper\Context\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Context\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Context\Path\PathInterface;

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
