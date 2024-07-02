<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\Attribute\TargetTemplateArgument;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\ArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\ArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class ArrayType implements LogicalTypeInterface
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
            arguments: new ArgumentsListNode([
                new ArgumentNode($this->key->getTypeStatement($context)),
                new ArgumentNode($this->value->getTypeStatement($context)),
            ]),
        );
    }

    /**
     * @return array<array-key, mixed>
     * @throws InvalidValueException
     */
    private function validateAndCast(mixed $value, LocalContext $context): array
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = match (true) {
                \is_array($value) => $value,
                $value instanceof \Traversable => \iterator_to_array($value, false),
                \is_string($value) => \str_split($value),
                default => [$value],
            };
        }

        if (!\is_array($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                context: $context,
                expectedType: $this->getTypeStatement($context),
                actualValue: $value,
            );
        }

        return $value;
    }

    public function supportsCasting(mixed $value, LocalContext $context): bool
    {
        return \is_array($value);
    }

    /**
     * @return array<array-key, mixed>
     * @throws InvalidValueException
     * @throws TypeNotFoundException
     */
    public function cast(mixed $value, RegistryInterface $types, LocalContext $context): array
    {
        $value = $this->validateAndCast($value, $context);

        $result = [];

        foreach ($value as $index => $item) {
            $context->enter($index);

            $result[$this->key->cast($index, $types, $context)]
                = $this->value->cast($item, $types, $context);

            $context->leave();
        }

        return $result;
    }
}
