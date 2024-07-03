<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Context\Path\ArrayIndexEntry;
use TypeLang\Mapper\Context\Path\ObjectPropertyEntry;

final class JsonPath extends Path
{
    public function __toString(): string
    {
        $result = '$';

        foreach ($this->only([ArrayIndexEntry::class, ObjectPropertyEntry::class]) as $entry) {
            $result .= match (true) {
                $entry instanceof ArrayIndexEntry => "[$entry]",
                $entry instanceof ObjectPropertyEntry => ".$entry",
            };
        }

        return $result;
    }
}
