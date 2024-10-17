<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use JetBrains\PhpStorm\Language;
use Phplrt\Contracts\Source\SourceExceptionInterface;
use Phplrt\Contracts\Source\SourceFactoryInterface;
use Phplrt\Source\SourceFactory;
use TypeLang\Parser\Exception\ParserExceptionInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Parser;
use TypeLang\Parser\ParserInterface;

final class InMemoryCachedParser implements ParserInterface
{
    /**
     * @var array<non-empty-string, TypeStatement>
     */
    private array $types = [];

    public function __construct(
        private readonly ParserInterface $parser = new Parser(),
        private readonly SourceFactoryInterface $sources = new SourceFactory(),
        /**
         * Limit on the number of statements stored in RAM.
         *
         * @var int<0, max>
         */
        private readonly int $typesLimit = 100,

        /**
         * Number of types cleared after GC triggering.
         *
         * @var int<0, max>
         */
        private readonly int $typesCleanupCount = 30,
    ) {}

    /**
     * @throws ParserExceptionInterface
     * @throws SourceExceptionInterface
     * @throws \Throwable
     */
    public function parse(#[Language('PHP')] mixed $source): TypeStatement
    {
        $instance = $this->sources->create($source);

        $this->gc();

        return $this->types[$instance->getHash()] ??= $this->parser->parse($source);
    }

    private function gc(): void
    {
        if (\count($this->types) <= $this->typesLimit) {
            return;
        }

        $this->types = \array_slice($this->types, $this->typesCleanupCount);
    }
}
