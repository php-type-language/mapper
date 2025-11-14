<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Parser;

use JetBrains\PhpStorm\Language;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class InMemoryTypeParser implements TypeParserInterface
{
    /**
     * @var array<non-empty-string, TypeStatement>
     */
    private array $types = [];

    /**
     * @var int<0, max>
     */
    public const DEFAULT_MAX_IN_MEMORY_TYPES = 100;

    /**
     * @var int<0, max>
     */
    public const DEFAULT_MIN_IN_MEMORY_TYPES = 30;

    public function __construct(
        private readonly TypeParserInterface $delegate,
        /**
         * Limit on the number of statements stored in RAM.
         *
         * @var int<0, max>
         */
        private readonly int $maxTypesLimit = self::DEFAULT_MAX_IN_MEMORY_TYPES,
        /**
         * Number of types cleared after GC triggering.
         *
         * @var int<0, max>
         */
        private readonly int $minTypesLimit = self::DEFAULT_MIN_IN_MEMORY_TYPES,
    ) {}

    /**
     * @param non-empty-string $definition
     *
     * @return non-empty-string
     */
    private function keyOf(string $definition): string
    {
        return $definition;
    }

    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
    {
        $this->cleanup();

        return $this->types[$this->keyOf($definition)]
            ??= $this->delegate->getStatementByDefinition($definition);
    }

    private function cleanup(): void
    {
        if (\count($this->types) <= $this->maxTypesLimit) {
            return;
        }

        $this->types = \array_slice($this->types, $this->minTypesLimit);
    }
}
