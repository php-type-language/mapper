<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;

/**
 * @template T as object
 *
 * @template-implements MapperInterface<T>
 */
final class InMemoryCachedMapper implements MapperInterface
{
    /**
     * @var array<non-empty-string, T>
     */
    private array $types = [];

    /**
     * @param MapperInterface<T> $mapper
     */
    public function __construct(
        private readonly MapperInterface $mapper,
    ) {}

    public function get(#[Language('PHP')] string $type): object
    {
        return $this->types[\hash('xxh3', $type)] ??= $this->mapper->get($type);
    }
}
