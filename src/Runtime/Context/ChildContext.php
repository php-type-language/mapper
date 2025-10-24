<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Context;

use TypeLang\Mapper\Runtime\ConfigurationInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Path\Entry\EntryInterface;
use TypeLang\Mapper\Runtime\Path\Path;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class ChildContext extends Context
{
    protected function __construct(
        public readonly Context $parent,
        private readonly EntryInterface $entry,
        mixed $value,
        Direction $direction,
        TypeExtractorInterface $extractor,
        TypeParserInterface $parser,
        TypeRepositoryInterface $types,
        ConfigurationInterface $config,
        private readonly ?bool $isStrictTypes = null,
    ) {
        parent::__construct(
            value: $value,
            direction: $direction,
            extractor: $extractor,
            parser: $parser,
            types: $types,
            config: $config,
        );
    }

    #[\Override]
    public function isStrictTypesEnabled(): bool
    {
        return $this->isStrictTypes
            ?? $this->config->isStrictTypesEnabled();
    }

    /**
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
