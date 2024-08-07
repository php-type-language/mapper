<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

final class FloatType implements LogicalTypeInterface
{
    /**
     * @var non-empty-string
     */
    private const DEFAULT_TYPE_NAME = 'float';

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        #[TargetTypeName]
        private readonly string $name = self::DEFAULT_TYPE_NAME,
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        return new NamedTypeNode($this->name);
    }

    public function supportsCasting(mixed $value, LocalContext $context): bool
    {
        return \is_float($value) || \is_int($value);
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, RegistryInterface $types, LocalContext $context): float
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->castToFloatIfPossible($value);
        }

        if (!\is_float($value) && !\is_int($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                context: $context,
                expectedType: new UnionTypeNode(
                    a: new NamedTypeNode('int'),
                    b: new NamedTypeNode('float'),
                ),
                actualValue: $value,
            );
        }

        return (float) $value;
    }

    /**
     * A method to convert input data to a float representation, if possible.
     *
     * If conversion is not possible, it returns the value "as is".
     */
    private function castToFloatIfPossible(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        return match (true) {
            \is_array($value),
            \is_object($value) => $value,
            \is_string($value) => match (true) {
                $value === '' => 0.0,
                \is_numeric($value) => (float) $value,
                default => 1.0,
            },
            // @phpstan-ignore-next-line : Any other type can be converted to float
            default => (float) $value,
        };
    }
}
