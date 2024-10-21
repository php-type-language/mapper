<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Context;

use TypeLang\Mapper\Runtime\ConfigurationInterface;
use TypeLang\Mapper\Runtime\ContextInterface;
use TypeLang\Mapper\Runtime\Path\Entry\EntryInterface;
use TypeLang\Mapper\Runtime\Path\Path;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Runtime\Repository\Repository;

abstract class Context implements ContextInterface
{
    public function __construct(
        protected readonly Direction $direction,
        protected readonly Repository $types,
        protected readonly ConfigurationInterface $config,
    ) {}

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

    public function getTypes(): Repository
    {
        return $this->types;
    }
}
