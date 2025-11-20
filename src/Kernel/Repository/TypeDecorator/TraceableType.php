<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Repository\TypeDecorator;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Type\MatchedResult;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Type\Repository
 *
 * @template-covariant TResult of mixed = mixed
 * @template-covariant TMatch of mixed = mixed
 *
 * @template-extends TypeDecorator<TResult, TMatch>
 */
final class TraceableType extends TypeDecorator
{
    /**
     * @var non-empty-string
     */
    private readonly string $name;

    /**
     * @param TypeInterface<TResult, TMatch> $delegate
     */
    public function __construct(
        private readonly bool $traceTypeMatching,
        private readonly bool $traceTypeCasting,
        private readonly string $definition,
        TypeInterface $delegate,
    ) {
        parent::__construct($delegate);

        $this->name = $this->getSpanTitle();
    }

    /**
     * @return non-empty-string
     */
    private function getInstanceName(object $entry): string
    {
        return $entry::class . '#' . \spl_object_id($entry);
    }

    /**
     * @return non-empty-string
     */
    private function getSpanTitle(): string
    {
        $realType = $this->getDecoratedType();

        return \vsprintf('"%s" using %s', [
            \addcslashes($this->definition, '"'),
            $this->getInstanceName($realType),
        ]);
    }

    public function match(mixed $value, RuntimeContext $context): ?MatchedResult
    {
        $tracer = $context->config->findTracer();

        if ($tracer === null || $this->traceTypeMatching === false) {
            return parent::match($value, $context);
        }

        $span = $tracer->start(\sprintf('Match %s', $this->name));

        try {
            return parent::match($value, $context);
        } finally {
            $span->stop();
        }
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        $tracer = $context->config->findTracer();

        if ($tracer === null || $this->traceTypeCasting === false) {
            return parent::cast($value, $context);
        }

        $span = $tracer->start(\sprintf('Cast %s', $this->name));

        try {
            return parent::cast($value, $context);
        } finally {
            $span->stop();
        }
    }
}
