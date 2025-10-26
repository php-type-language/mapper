<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Context\Path\Entry\EntryInterface;
use TypeLang\Mapper\Context\Path\Path;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Mapper\Runtime\ConfigurationInterface;
use TypeLang\Mapper\Runtime\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class ChildContext extends Context
{
    protected function __construct(
        public readonly Context $parent,
        private readonly EntryInterface $entry,
        mixed $value,
        Direction $direction,
        ConfigurationInterface $config,
        TypeExtractorInterface $extractor,
        TypeParserInterface $parser,
        TypeRepositoryInterface $types,
        private readonly ?bool $overrideStrictTypes = null,
        private readonly ?bool $overrideObjectAsArray = null,
    ) {
        parent::__construct(
            value: $value,
            direction: $direction,
            config: $config,
            extractor: $extractor,
            parser: $parser,
            types: $types,
        );
    }

    #[\Override]
    public function isStrictTypesEnabled(): bool
    {
        return $this->overrideStrictTypes
            ?? parent::isStrictTypesEnabled();
    }

    #[\Override]
    public function isObjectAsArray(): bool
    {
        return $this->overrideObjectAsArray
            ?? parent::isObjectAsArray();
    }

    /**
     * Gets parent context
     *
     * @api
     */
    public function getParent(): Context
    {
        return $this->parent;
    }

    /**
     * @return iterable<array-key, Context>
     */
    private function getStack(): iterable
    {
        yield $current = $this;

        do {
            yield $current = $current->parent;
        } while ($current instanceof self);
    }

    public function getPath(): PathInterface
    {
        $entries = [];

        foreach ($this->getStack() as $context) {
            if ($context instanceof self) {
                $entries[] = $context->entry;
            }
        }

        return new Path(\array_reverse($entries));
    }
}
