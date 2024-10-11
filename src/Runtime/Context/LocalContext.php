<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Context;

use TypeLang\Mapper\Runtime\ExecutionStackInterface;
use TypeLang\Mapper\Runtime\Path\Entry\EntryInterface;
use TypeLang\Mapper\Runtime\Path\MutablePath;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;

/**
 * Mutable local bypass context.
 */
class LocalContext extends Context implements ExecutionStackInterface
{
    protected readonly MutablePath $path;

    final public function __construct(
        private readonly Direction $direction,
        private readonly RepositoryInterface $types,
        ?bool $objectsAsArrays = null,
        ?bool $detailedTypes = null,
    ) {
        $this->path = new MutablePath();

        parent::__construct(
            objectsAsArrays: $objectsAsArrays,
            detailedTypes: $detailedTypes,
        );
    }

    public static function fromContext(Direction $direction, RepositoryInterface $types, Context $context): self
    {
        return new self(
            direction: $direction,
            types: $types,
            objectsAsArrays: $context->objectsAsArrays,
            detailedTypes: $context->detailedTypes,
        );
    }

    /**
     * @api
     */
    public function isNormalization(): bool
    {
        return $this->getDirection() === Direction::Normalize;
    }

    /**
     * @api
     */
    public function isDenormalization(): bool
    {
        return $this->getDirection() === Direction::Denormalize;
    }

    /**
     * @api
     */
    public function getDirection(): Direction
    {
        return $this->direction;
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
