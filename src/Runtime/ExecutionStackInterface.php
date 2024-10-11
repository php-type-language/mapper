<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

use TypeLang\Mapper\Runtime\Path\Entry\EntryInterface;

interface ExecutionStackInterface
{
    public function enter(EntryInterface $entry): void;

    public function leave(): void;
}
