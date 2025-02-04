<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository\TypeDecorator;

use TypeLang\Mapper\Platform\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Tracing\TracerInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Runtime\Repository
 */
final class TraceableType extends TypeDecorator
{
    /**
     * @var non-empty-string
     */
    private readonly string $name;

    public function __construct(
        private readonly string $definition,
        private readonly TracerInterface $tracer,
        TypeInterface $delegate,
    ) {
        parent::__construct($delegate);

        $this->name = $this->getSpanTitle();
    }

    /**
     * @return non-empty-string
     */
    private function getSpanTitle(): string
    {
        $inner = $this->getDecoratedType();

        return \vsprintf('"%s" using %s#%d', [
            \addcslashes($this->definition, '"'),
            $inner::class,
            \spl_object_id($inner),
        ]);
    }

    public function match(mixed $value, Context $context): bool
    {
        $span = $this->tracer->start(\sprintf('Match %s', $this->name));

        try {
            return parent::match($value, $context);
        } finally {
            $span->stop();
        }
    }

    public function cast(mixed $value, Context $context): mixed
    {
        $span = $this->tracer->start(\sprintf('Cast %s', $this->name));

        try {
            return parent::cast($value, $context);
        } finally {
            $span->stop();
        }
    }
}
