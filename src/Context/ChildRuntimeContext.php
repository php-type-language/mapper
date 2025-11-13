<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Path\Entry\EntryInterface;
use TypeLang\Mapper\Context\Path\Path;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;

final class ChildRuntimeContext extends RuntimeContext
{
    protected function __construct(
        public readonly RuntimeContext $parent,
        public readonly EntryInterface $entry,
        mixed $value,
        DirectionInterface $direction,
        Configuration $config,
        TypeExtractorInterface $extractor,
        TypeParserInterface $parser,
        TypeRepositoryInterface $types,
        ?Configuration $original = null,
    ) {
        parent::__construct(
            value: $value,
            direction: $direction,
            config: $config,
            extractor: $extractor,
            parser: $parser,
            types: $types,
            original: $original,
        );
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
