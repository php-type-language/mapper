<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Runtime\Context\ChildContext;
use TypeLang\Mapper\Runtime\Context\DirectionInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Path\Entry\EntryInterface;
use TypeLang\Mapper\Runtime\Path\Path;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Runtime\Path\PathProviderInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class Context implements
    TypeRepositoryInterface,
    ConfigurationInterface,
    PathProviderInterface,
    TypeParserInterface,
    DirectionInterface
{
    protected function __construct(
        protected readonly DirectionInterface $direction,
        protected readonly TypeRepository $types,
        protected readonly ConfigurationInterface $config,
    ) {}

    /**
     * Creates new child context.
     */
    public function enter(EntryInterface $entry): self
    {
        return new ChildContext(
            parent: $this,
            entry: $entry,
            direction: $this->direction,
            types: $this->types,
            config: $this->config,
        );
    }

    public function isObjectsAsArrays(): bool
    {
        return $this->config->isObjectsAsArrays();
    }

    public function isDetailedTypes(): bool
    {
        return $this->config->isDetailedTypes();
    }

    public function isNormalization(): bool
    {
        return $this->direction->isNormalization();
    }

    public function isDenormalization(): bool
    {
        return $this->direction->isDenormalization();
    }

    public function getPath(): PathInterface
    {
        return new Path();
    }

    public function getByType(string $type, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->types->getByType($type, $context);
    }

    public function getByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->types->getByValue($value, $context);
    }

    public function getByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->types->getByStatement($statement, $context);
    }

    public function getStatementByType(#[Language('PHP')] string $type): TypeStatement
    {
        return $this->types->getStatementByValue($type);
    }

    public function getStatementByValue(mixed $value): TypeStatement
    {
        return $this->types->getStatementByValue($value);
    }
}
