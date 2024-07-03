<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Context\Path\EntryInterface;

/**
 * @template-extends \Traversable<array-key, EntryInterface>
 */
interface PathInterface extends \Traversable, \Countable
{
    public function enter(EntryInterface $entry): void;

    public function leave(): void;

    public function contains(mixed $value): bool;
}
