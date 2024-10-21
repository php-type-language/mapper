<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

use TypeLang\Mapper\Runtime\Context\DirectionInterface;
use TypeLang\Mapper\Runtime\Path\Entry\EntryInterface;
use TypeLang\Mapper\Runtime\Path\PathProviderInterface;
use TypeLang\Mapper\Runtime\Repository\Repository;

interface ContextInterface extends
    ConfigurationInterface,
    PathProviderInterface,
    DirectionInterface
{
    /**
     * Creates new child context.
     */
    public function enter(EntryInterface $entry): self;

    /**
     * @deprecated will be rewritten to direct types repository access.
     */
    public function getTypes(): Repository;
}
