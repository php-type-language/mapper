<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Context;

use TypeLang\Mapper\Runtime\ConfigurationInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\Entry\EntryInterface;
use TypeLang\Mapper\Runtime\Path\Path;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Runtime\Repository\Repository;

final class ChildContext extends Context
{
    public function __construct(
        private readonly Context $parent,
        private readonly EntryInterface $entry,
        DirectionInterface $direction,
        Repository $types,
        ConfigurationInterface $config,
    ) {
        parent::__construct(
            direction: $direction,
            types: $types,
            config: $config,
        );
    }

    /**
     * @return iterable<array-key, Context>
     */
    private function getStack(): iterable
    {
        yield $current = $this;

        do {
            yield $current = $current->parent;
        } while ($current instanceof self);
    }

    public function getPath(): PathInterface
    {
        $entries = [];

        foreach ($this->getStack() as $context) {
            if ($context instanceof self) {
                $entries[] = $context->entry;
            }
        }

        return new Path($entries);
    }
}
