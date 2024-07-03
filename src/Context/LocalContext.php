<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Context;
use TypeLang\Mapper\Context\Path\ArrayIndexEntry;
use TypeLang\Mapper\Context\Path\EntryInterface;
use TypeLang\Mapper\Context\Path\ObjectPropertyEntry;

/**
 * Mutable local bypass context.
 */
final class LocalContext extends Context
{
    private readonly PathInterface $path;

    final public function __construct(
        private readonly Direction $direction,
        ?bool $strictTypes = null,
        ?bool $objectsAsArrays = null,
        ?bool $detailedTypes = null,
    ) {
        $this->path = new Path();

        parent::__construct(
            strictTypes: $strictTypes,
            objectsAsArrays: $objectsAsArrays,
            detailedTypes: $detailedTypes,
        );
    }

    public static function fromContext(Direction $direction, ?Context $context): self
    {
        return (new self($direction))
            ->merge($context);
    }

    #[\Override]
    public function merge(?Context $context): self
    {
        if ($context === null) {
            return $this;
        }

        return new self(
            direction: $context instanceof self ? $context->direction : $this->direction,
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

    public function contains(mixed $value): bool
    {
        return $this->path->contains($value);
    }

    /**
     * @return $this
     */
    public function enter(EntryInterface $item): self
    {
        $this->path->enter($item);

        return $this;
    }

    public function leave(): void
    {
        $this->path->leave();
    }
}
