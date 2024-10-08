<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Path;

use TypeLang\Mapper\Path\Entry\EntryInterface;

/**
 * @template-extends \Traversable<array-key, EntryInterface>
 */
interface PathInterface extends \Traversable, \Countable
{
    /**
     * @return list<EntryInterface>
     */
    public function toArray(): array;

    /**
     * Returns {@see true} in case of mapping path is empty.
     */
    public function isEmpty(): bool;
}