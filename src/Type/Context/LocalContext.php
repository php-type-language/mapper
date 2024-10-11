<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Context;

use TypeLang\Mapper\Path\Entry\EntryInterface;
use TypeLang\Mapper\Path\ExecutionStackInterface;
use TypeLang\Mapper\Path\MutablePath;
use TypeLang\Mapper\Path\PathInterface;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;

/**
 * Mutable local bypass context.
 */
final class LocalContext extends Context implements ExecutionStackInterface
{
    private readonly MutablePath $path;

    private readonly MutablePath $trace;

    final public function __construct(
        private readonly Direction $direction,
        private readonly RepositoryInterface $types,
        ?bool $objectsAsArrays = null,
        ?bool $detailedTypes = null,
    ) {
        $this->path = new MutablePath();
        $this->trace = new MutablePath();

        parent::__construct(
            objectsAsArrays: $objectsAsArrays,
            detailedTypes: $detailedTypes,
        );
    }

    public static function fromContext(
        Direction $direction,
        RepositoryInterface $types,
        ?Context $context,
    ): self {
        $instance = new self($direction, $types);

        return $instance->with($context);
    }

    #[\Override]
    public function with(?Context $context): self
    {
        if ($context === null) {
            return $this;
        }

        $local = $context instanceof self ? $context : $this;

        return new self(
            direction: $local->direction,
            types: $local->types,
            objectsAsArrays: $context->objectsAsArrays ?? $this->objectsAsArrays,
            detailedTypes: $context->detailedTypes ?? $this->detailedTypes,
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
    public function getTrace(): PathInterface
    {
        return $this->trace;
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
        $this->trace->enter($entry);
    }

    public function leave(): void
    {
        $this->path->leave();
    }
}
