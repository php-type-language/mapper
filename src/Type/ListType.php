<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Path\Entry\ArrayIndexEntry;
use TypeLang\Mapper\Type\Attribute\TargetTemplateArgument;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\ArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\ArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class ListType implements LogicalTypeInterface
{
    /**
     * @var non-empty-string
     */
    private const DEFAULT_TYPE_NAME = 'list';

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        #[TargetTypeName]
        private readonly string $name = self::DEFAULT_TYPE_NAME,
        #[TargetTemplateArgument]
        private readonly TypeInterface $type = new MixedType(),
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        if (!$context->isDetailedTypes()) {
            return new NamedTypeNode($this->name);
        }

        return new NamedTypeNode(
            name: $this->name,
            arguments: new ArgumentsListNode([
                new ArgumentNode($this->type->getTypeStatement($context)),
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
                expectedType: 'array',
                actualValue: $value,
            );
        }

        return $value;
    }

    public function supportsCasting(mixed $value, LocalContext $context): bool
    {
        return \is_array($value) && \array_is_list($value);
    }

    /**
     * @return list<mixed>
     * @throws InvalidValueException
     * @throws TypeNotFoundException
     */
    public function cast(mixed $value, RepositoryInterface $types, LocalContext $context): array
    {
        $value = $this->validateAndCast($value, $context);

        $result = [];

        foreach ($value as $index => $item) {
            $context->enter(new ArrayIndexEntry($index));

            $result[] = $this->type->cast($item, $types, $context);

            $context->leave();
        }

        return $result;
    }
}
