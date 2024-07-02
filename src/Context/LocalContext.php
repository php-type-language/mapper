<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Context;

/**
 * Mutable local bypass context.
 */
final class LocalContext extends Context
{
    /**
     * @var list<non-empty-string|int>
     */
    private array $stack = [];

    final public function __construct(
        private readonly Direction $direction,
        ?bool $strictTypes = null,
        ?bool $objectsAsArrays = null,
        ?bool $detailedTypes = null,
    ) {
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

        $result = new self(
            direction: $context instanceof self ? $context->direction : $this->direction,
            strictTypes: $context->strictTypes ?? $this->strictTypes,
            objectsAsArrays: $context->objectsAsArrays ?? $this->objectsAsArrays,
            detailedTypes: $context->detailedTypes ?? $this->detailedTypes,
        );

        if ($context instanceof self) {
            $result->stack = $context->stack;
        }

        return $result;
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
    public function getPath(): array
    {
        return $this->stack;
    }

    /**
     * @param non-empty-string|int $item
     *
     * @return $this
     */
    public function enter(string|int $item): self
    {
        $this->stack[] = $item;

        return $this;
    }

    public function leave(): void
    {
        if ($this->stack !== []) {
            \array_pop($this->stack);
        }
    }
}
