<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

use TypeLang\Mapper\Runtime\Context\Direction;
use TypeLang\Mapper\Runtime\Context\DirectionInterface;
use TypeLang\Mapper\Runtime\Path\Entry\EntryInterface;
use TypeLang\Mapper\Runtime\Path\MutablePath;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;

class Context implements
    ExecutionStackInterface,
    ConfigurationInterface,
    DirectionInterface
{
    protected readonly MutablePath $path;

    final public function __construct(
        private readonly Direction $direction,
        private readonly RepositoryInterface $types,
        private readonly ConfigurationInterface $config,
    ) {
        $this->path = new MutablePath();
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

    /**
     * @api
     */
    public function getPath(): PathInterface
    {
        return $this->path;
    }

    /**
     * @api
     */
    public function getTypes(): RepositoryInterface
    {
        return $this->types;
    }

    public function enter(EntryInterface $entry): void
    {
        $this->path->enter($entry);
    }

    public function leave(): void
    {
        $this->path->leave();
    }
}
