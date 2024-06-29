<?php

declare(strict_types=1);

namespace Serafim\Mapper\Context;

use Serafim\Mapper\Context;
use Serafim\Mapper\Context\Path\PathInterface;
use Serafim\Mapper\Context\Path\PathType;

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
        $result = (new self())->with($context);

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
