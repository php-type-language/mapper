<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

class ArrayType implements TypeInterface
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_TYPE_NAME = 'array';

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        protected readonly string $name = self::DEFAULT_TYPE_NAME,
        protected readonly TypeInterface $key = new ArrayKeyType(),
        protected readonly TypeInterface $value = new MixedType(),
    ) {}

    #[\Override]
    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        if (!$context->isDetailedTypes()) {
            return new NamedTypeNode($this->name);
        }

        return new NamedTypeNode(
            name: $this->name,
            arguments: new TemplateArgumentsListNode([
                new TemplateArgumentNode($this->key->getTypeStatement($context)),
                new TemplateArgumentNode($this->value->getTypeStatement($context)),
            ]),
        );
    }

    /**
     * @return UnionTypeNode<TypeStatement>
     */
    protected function getSupportedKeyType(): UnionTypeNode
    {
        return new UnionTypeNode(
            new NamedTypeNode('string'),
            new NamedTypeNode('int'),
        );
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        return $this->matchRootType($value, $context);
    }

    /**
     * @return ($value is iterable ? true : false)
     */
    private function matchRootType(mixed $value, LocalContext $context): bool
    {
        if (!$context->isStrictTypesEnabled()) {
            return \is_iterable($value);
        }

        return \is_array($value);
    }

    /**
     * @return array<array-key, mixed>
     * @throws InvalidValueException
     * @throws TypeNotFoundException
     */
    public function cast(mixed $value, LocalContext $context): array
    {
        if (!$this->matchRootType($value, $context)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        $result = [];

        foreach ($value as $index => $item) {
            if (!\is_string($index) && !\is_int($index)) {
                throw InvalidValueException::becauseInvalidValueGiven(
                    value: $index,
                    expected: $this->getSupportedKeyType(),
                    context: $context,
                );
            }

            $context->enter(new ArrayIndexEntry($index));

            $result[$this->key->cast($index, $context)]
                = $this->value->cast($item, $context);

            $context->leave();
        }

        return $result;
    }
}
