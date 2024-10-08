<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Context;

use TypeLang\Mapper\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Path\Entry\EntryInterface;
use TypeLang\Mapper\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Path\ExecutionStackInterface;
use TypeLang\Mapper\Path\MutablePath;
use TypeLang\Mapper\Path\PathInterface;

/**
 * Mutable local bypass context.
 */
final class LocalContext extends Context implements ExecutionStackInterface
{
    private readonly MutablePath $path;

    final public function __construct(
        private readonly Direction $direction,
        ?bool $strictTypes = null,
        ?bool $objectsAsArrays = null,
        ?bool $detailedTypes = null,
    ) {
        $this->path = new MutablePath();

        parent::__construct(
            strictTypes: $strictTypes,
            objectsAsArrays: $objectsAsArrays,
            detailedTypes: $detailedTypes,
        );
    }

    public static function fromContext(Direction $direction, ?Context $context): self
    {
        return (new self($direction))
            ->with($context);
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
            strictTypes: $context->strictTypes ?? $this->strictTypes,
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
     * @return list<non-empty-string|int>
     *
     * @deprecated Will be removed
     */
    public function getPathAsSegmentsArray(): array
    {
        $result = [];

        foreach ($this->path as $entry) {
            switch (true) {
                case $entry instanceof ArrayIndexEntry:
                    $result[] = $entry->index;
                    break;

                case $entry instanceof ObjectPropertyEntry:
                    $result[] = $entry->value;
                    break;
            }
        }

        return $result;
    }

    public function getPath(): PathInterface
    {
        return $this->path;
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
