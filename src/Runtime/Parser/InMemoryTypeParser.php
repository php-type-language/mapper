<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser;

use TypeLang\Parser\Node\Stmt\TypeStatement;

final class InMemoryTypeParser implements TypeParserInterface
{
    /**
     * @var array<non-empty-string, TypeStatement>
     */
    private array $types = [];

    public function __construct(
        private readonly TypeParserInterface $delegate,
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

    public function getStatementByDefinition(string $definition): TypeStatement
    {
        $this->cleanup();

        return $this->types[$definition]
            ??= $this->delegate->getStatementByDefinition($definition);
    }

    private function cleanup(): void
    {
        if (\count($this->types) <= $this->typesLimit) {
            return;
        }

        $this->types = \array_slice($this->types, $this->typesCleanupCount);
    }
}
