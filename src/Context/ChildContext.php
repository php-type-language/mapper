<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Path\Entry\EntryInterface;
use TypeLang\Mapper\Context\Path\Path;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Mapper\Kernel\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Kernel\Parser\TypeParserInterface;
use TypeLang\Mapper\Kernel\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Platform\PlatformInterface;

final class ChildContext extends RuntimeContext
{
    protected function __construct(
        /**
         * @readonly
         *
         * @phpstan-readonly-allow-private-mutation
         */
        public RuntimeContext $parent,
        /**
         * @readonly
         *
         * @phpstan-readonly-allow-private-mutation
         */
        public EntryInterface $entry,
        mixed $value,
        DirectionInterface $direction,
        TypeRepositoryInterface $types,
        TypeExtractorInterface $extractor,
        TypeParserInterface $parser,
        PlatformInterface $platform,
        Configuration $config,
        /**
         * Contains a reference to the original config created during this
         * context initialization.
         *
         * If there is no reference ({@see null}), then the current config in
         * the {@see $config} context's field is the original.
         *
         * @readonly
         *
         * @phpstan-readonly-allow-private-mutation
         */
        public ?Configuration $original = null,
    ) {
        parent::__construct(
            value: $value,
            direction: $direction,
            types: $types,
            parser: $parser,
            extractor: $extractor,
            platform: $platform,
            config: $config,
        );
    }

    /**
     * Optimized entering using clone
     */
    #[\Override]
    public function enter(mixed $value, EntryInterface $entry, ?Configuration $config = null): self
    {
        // Original configuration
        $original = $this->original ?? $this->config;

        // Configuration of the current context
        $current = $config ?? $original;

        // Do not set "previous" config in case of
        // "current" config is original
        if ($current === $original) {
            $original = null;
        }

        $self = clone $this;
        $self->parent = $this;
        $self->entry = $entry;
        /** @phpstan-ignore-next-line : Allow mutation from protected scope */
        $self->value = $value;
        /** @phpstan-ignore-next-line : Allow mutation from protected scope */
        $self->config = $current;
        $self->original = $current === $original ? null : $original;

        return $self;
    }

    /**
     * Sets the value of the "object as array" configuration settings using
     * the original configuration rules.
     *
     * Note that the {@see $config} property contains the **current** context
     * configuration settings, which may differ from the original ones.
     * Therefore, method {@see RuntimeContext::withObjectAsArray()} is not equivalent
     * to calling {@see Configuration::withObjectAsArray()}.
     */
    #[\Override]
    public function withObjectAsArray(?bool $enabled): Configuration
    {
        if ($enabled === null) {
            return $this->original ?? $this->config;
        }

        return ($this->original ?? $this->config)
            ->withObjectAsArray($enabled);
    }

    /**
     * Sets the value of the "strict types" configuration settings using
     * the original configuration rules.
     *
     * Note that the {@see $config} property contains the **current** context
     * configuration settings, which may differ from the original ones.
     * Therefore, method {@see RuntimeContext::withStrictTypes()} is not equivalent
     * to calling {@see Configuration::withStrictTypes()}.
     */
    #[\Override]
    public function withStrictTypes(?bool $enabled): Configuration
    {
        if ($enabled === null) {
            return $this->original ?? $this->config;
        }

        return ($this->original ?? $this->config)
            ->withStrictTypes($enabled);
    }

    public function getIterator(): \Traversable
    {
        yield $current = $this;

        do {
            yield $current = $current->parent;
        } while ($current instanceof self);
    }

    public function getPath(): PathInterface
    {
        $entries = [];

        foreach ($this as $context) {
            if ($context instanceof self) {
                $entries[] = $context->entry;
            }
        }

        return new Path(\array_reverse($entries));
    }
}
