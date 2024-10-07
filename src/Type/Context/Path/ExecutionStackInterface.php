<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Context\Path;

use TypeLang\Mapper\Type\Context\Path\Entry\EntryInterface;

interface ExecutionStackInterface
{
    public function enter(EntryInterface $entry): void;

    public function leave(): void;
}
