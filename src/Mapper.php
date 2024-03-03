<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Exception\InvalidTypeNameException;
use TypeLang\Mapper\Exception\MapperExceptionInterface;
use TypeLang\Mapper\Exception\TypeException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Factory\OptionalTypeFactoryInterface;
use TypeLang\Mapper\Factory\Repository;
use TypeLang\Mapper\Factory\TypeFactoryInterface;
use TypeLang\Parser\Node\NodeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Parser;
use TypeLang\Parser\ParserInterface;

/**
 * @template T of object
 *
 * @template-implements MapperInterface<T>
 * @template-implements TypeFactoryInterface<T>
 *
 * @psalm-suppress UndefinedAttributeClass
 */
class Mapper implements MapperInterface, TypeFactoryInterface
{
    /**
     * @var Repository<T>
     */
    protected readonly Repository $types;

    /**
     * @param iterable<array-key, OptionalTypeFactoryInterface<T>> $types
     */
    public function __construct(
        iterable $types = [],
        private readonly ParserInterface $parser = new Parser(
            callables: false,
        ),
    ) {
        $this->types = new Repository($types);
    }

    public function get(#[Language('PHP')] string $type): object
    {
        /** @psalm-suppress TypeDoesNotContainType */
        if ($type === '' || \trim($type) === '') {
            throw InvalidTypeNameException::fromEmptyTypeName();
        }

        return $this->create($this->parse($type), $this);
    }

    /**
     * @param OptionalTypeFactoryInterface<T> $factory
     *
     * @return T
     *
     * @throws MapperExceptionInterface
     */
    public function create(NodeInterface $node, OptionalTypeFactoryInterface $factory): object
    {
        foreach ($this->types as $factory) {
            if ($result = $factory->create($node, $this)) {
                return $result;
            }
        }

        if ($node instanceof NamedTypeNode) {
            throw TypeNotFoundException::fromNonFoundType($node->name->toString());
        }

        throw TypeNotFoundException::fromNonParsableType($node::class);
    }

    /**
     * @throws TypeException
     */
    public function parse(#[Language('PHP')] string $type): NodeInterface
    {
        try {
            $result = $this->parser->parse($type);
        } catch (\Throwable $e) {
            throw TypeException::fromInternalParsingException($type, $e);
        }

        if ($result === null) {
            throw TypeException::fromInternalParsingError($type);
        }

        return $result;
    }
}
