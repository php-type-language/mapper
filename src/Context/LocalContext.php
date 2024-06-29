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

    public static function fromContext(?Context $context): self
    {
        return (new self())->with($context);
    }

    public function with(?Context $context): static
    {
        if ($context === null) {
            return $this;
        }

        $result = new self(
            strictTypes: $context->strictTypes ?? $this->strictTypes,
            objectsAsArrays: $context->objectsAsArrays ?? $this->objectsAsArrays,
        );

        if ($context instanceof self) {
            $result->stack = $context->stack;
        }

        return $result;
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
