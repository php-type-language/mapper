<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Common\InteractWithTypeRepository;
use TypeLang\Mapper\Context\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Context\Path\Entry\EntryInterface;
use TypeLang\Mapper\Context\Path\Entry\ObjectEntry;
use TypeLang\Mapper\Context\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Context\Path\Entry\UnionLeafEntry;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Mapper\Kernel\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Kernel\Parser\TypeParserInterface;
use TypeLang\Mapper\Kernel\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Platform\PlatformInterface;

/**
 * @template-implements \IteratorAggregate<array-key, RuntimeContext>
 */
abstract class RuntimeContext extends MapperContext implements
    TypeRepositoryInterface,
    \IteratorAggregate,
    \Countable
{
    use InteractWithTypeRepository;

    protected function __construct(
        /**
         * Gets original (unmodified) value.
         *
         * Please note that the value may be changed during type manipulation
         * (casting), for example, using {@see TypeCoercerInterface}.
         *
         * In this case, the `$value` in the {@see RuntimeContext} remains the original
         * value, without any mutations from type coercions.
         *
         * @readonly
         *
         * @phpstan-readonly-allow-private-mutation
         */
        public mixed $value,
        /**
         * Gets data transformation direction.
         */
        public readonly DirectionInterface $direction,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
        TypeExtractorInterface $extractor,
        PlatformInterface $platform,
        Configuration $config,
    ) {
        parent::__construct(
            parser: $parser,
            extractor: $extractor,
            platform: $platform,
            config: $config,
        );

        $this->types = $types;
    }

    /**
     * Creates new child context.
     */
    public function enter(mixed $value, EntryInterface $entry, ?Configuration $config = null): self
    {
        [$original, $override] = $config === null
            ? [null, $this->config]
            : [$this->config, $config];

        return new ChildContext(
            parent: $this,
            entry: $entry,
            value: $value,
            direction: $this->direction,
            types: $this->types,
            extractor: $this->extractor,
            parser: $this->parser,
            platform: $this->platform,
            config: $override,
            original: $original,
        );
    }

    /**
     * Creates an "array index" child context
     *
     * @api
     *
     * @param array-key $index
     */
    public function enterIntoArrayIndex(mixed $value, int|string $index, ?Configuration $config = null): self
    {
        return $this->enter($value, new ArrayIndexEntry($index), $config);
    }

    /**
     * Creates an "object" child context
     *
     * @api
     *
     * @param class-string $class
     */
    public function enterIntoObject(mixed $value, string $class, ?Configuration $config = null): self
    {
        return $this->enter($value, new ObjectEntry($class), $config);
    }

    /**
     * Creates an "object's property" child context
     *
     * @api
     *
     * @param non-empty-string $name
     */
    public function enterIntoObjectProperty(mixed $value, string $name, ?Configuration $override = null): self
    {
        return $this->enter($value, new ObjectPropertyEntry($name), $override);
    }

    /**
     * Creates an "union leaf" child context
     *
     * @api
     *
     * @param int<0, max> $index
     */
    public function enterIntoUnionLeaf(mixed $value, int $index, ?Configuration $override = null): self
    {
        return $this->enter($value, new UnionLeafEntry($index), $override);
    }

    /**
     * Sets the value of the "object as array" configuration settings using
     * the original configuration rules.
     */
    public function withObjectAsArray(?bool $enabled): Configuration
    {
        return $this->config->withObjectAsArray($enabled);
    }

    /**
     * Sets the value of the "strict types" configuration settings using
     * the original configuration rules.
     */
    public function withStrictTypes(?bool $enabled): Configuration
    {
        return $this->config->withStrictTypes($enabled);
    }

    /**
     * Returns current context path.
     *
     * The {@see PathInterface} contains all occurrences used in "parent"
     * (composite) types when calling {@see enter()} method.
     */
    abstract public function getPath(): PathInterface;

    /**
     * @return int<1, max>
     */
    public function count(): int
    {
        return \max(1, \iterator_count($this));
    }
}
