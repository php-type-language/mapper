<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Type\Attribute\TargetTemplateArgument;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;
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
    private const DEFAULT_TYPE_NAME = 'array';

    private readonly TypeInterface $key;

    private readonly TypeInterface $value;

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        #[TargetTypeName]
        private readonly string $name = self::DEFAULT_TYPE_NAME,
        #[TargetTemplateArgument]
        ?TypeInterface $key = null,
        #[TargetTemplateArgument]
        ?TypeInterface $value = null,
    ) {
        [$this->key, $this->value] = match (true) {
            $key !== null && $value !== null => [$key, $value],
            $key === null && $value !== null => [new ArrayKeyType(), $value],
            $key !== null && $value === null => [new ArrayKeyType(), $key],
            default => [new ArrayKeyType(), new MixedType()],
        };
    }

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
