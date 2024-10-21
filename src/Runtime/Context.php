<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

use TypeLang\Mapper\Runtime\Context\ChildContext;
use TypeLang\Mapper\Runtime\Context\Direction;
use TypeLang\Mapper\Runtime\Context\DirectionInterface;
use TypeLang\Mapper\Runtime\Path\Entry\EntryInterface;
use TypeLang\Mapper\Runtime\Path\Path;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Runtime\Path\PathProviderInterface;
use TypeLang\Mapper\Runtime\Repository\Repository;

abstract class Context implements
    ConfigurationInterface,
    PathProviderInterface,
    DirectionInterface
{
    public function __construct(
        protected readonly DirectionInterface $direction,
        protected readonly Repository $types,
        protected readonly ConfigurationInterface $config,
    ) {}

    /**
     * Creates new child context.
     */
    public function enter(EntryInterface $entry): self
    {
        return new ChildContext(
            parent: $this,
            entry: $entry,
            direction: $this->direction,
            types: $this->types,
            config: $this->config,
        );
    }

    public function isObjectsAsArrays(): bool
    {
        return $this->config->isObjectsAsArrays();
    }

    public function isDetailedTypes(): bool
    {
        return $this->config->isDetailedTypes();
    }

    public function isNormalization(): bool
    {
        return $this->direction->isNormalization();
    }

    public function isDenormalization(): bool
    {
        return $this->direction->isDenormalization();
    }

    public function getPath(): PathInterface
    {
        return new Path();
    }

    /**
     * @deprecated will be rewritten to direct types repository access.
     */
    public function getTypes(): Repository
    {
        return $this->types;
    }
}
