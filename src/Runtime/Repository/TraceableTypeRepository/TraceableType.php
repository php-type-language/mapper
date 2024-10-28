<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository\TraceableTypeRepository;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\Entry\ObjectEntry;
use TypeLang\Mapper\Runtime\Tracing\TracerInterface;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Runtime\Repository
 */
final class TraceableType implements TypeInterface
{
    /**
     * @var non-empty-string
     */
    private readonly string $name;

    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly TypeInterface $delegate,
    ) {
        $this->name = self::getShortName($this->delegate::class)
            . '#' . \spl_object_id($this->delegate);
    }

    /**
     * @param non-empty-string $fqn
     *
     * @return non-empty-string
     */
    private static function getShortName(string $fqn): string
    {
        /** @var non-empty-list<non-empty-string> $parts */
        $parts = \explode('\\', $fqn);

        return \end($parts);
    }

    /**
     * @return list<non-empty-string>
     */
    private static function getPath(Context $context): array
    {
        $result = [];

        foreach ($context->getPath() as $entry) {
            $result[] = match (true) {
                $entry instanceof ObjectEntry => self::getShortName($entry->value),
                default => (string) $entry,
            };
        }

        /** @var list<non-empty-string> */
        return \array_reverse($result);
    }

    private static function getCurrentPath(Context $context): string
    {
        $result = self::getPath($context);

        if ($result === []) {
            return '<root>';
        }

        return \end($result);
    }

    public function match(mixed $value, Context $context): bool
    {
        $span = $this->tracer->start(\vsprintf('Type matching [%s at %s]', [
            $this->name,
            self::getCurrentPath($context),
        ]));

        try {
            $span->setAttribute('value', $value);
            $span->setAttribute('direction', $context->isNormalization() ? 'normalization' : 'denormalization');
            $span->setAttribute('path', self::getPath($context));

            $result = $this->delegate->match($value, $context);

            $span->setAttribute('result', $result);

            return $result;
        } finally {
            $span->stop();
        }
    }

    public function cast(mixed $value, Context $context): mixed
    {
        $span = $this->tracer->start(\vsprintf('Type casting [%s at %s]', [
            $this->name,
            self::getCurrentPath($context),
        ]));

        try {
            $span->setAttribute('value', $value);
            $span->setAttribute('direction', $context->isNormalization() ? 'normalization' : 'denormalization');
            $span->setAttribute('path', self::getPath($context));

            $result = $this->delegate->cast($value, $context);

            $span->setAttribute('result', $result);

            return $result;
        } finally {
            $span->stop();
        }
    }
}
