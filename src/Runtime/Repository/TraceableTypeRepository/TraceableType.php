<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository\TraceableTypeRepository;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Tracing\TracerInterface;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Runtime\Repository
 */
final class TraceableType implements TypeInterface
{
    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly TypeInterface $delegate,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        $span = $this->tracer->start(\sprintf(
            'type-lang::matching(%s)',
            $this->delegate::class . '#' . \spl_object_id($this->delegate),
        ));

        try {
            return $this->delegate->match($value, $context);
        } finally {
            $span->stop();
        }
    }

    public function cast(mixed $value, Context $context): mixed
    {
        $span = $this->tracer->start(\sprintf(
            'type-lang::casting(%s)',
            $this->delegate::class . '#' . \spl_object_id($this->delegate),
        ));

        try {
            return $this->delegate->cast($value, $context);
        } finally {
            $span->stop();
        }
    }
}
