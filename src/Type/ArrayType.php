<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\Attribute\TargetTemplateArgument;

final class ArrayType implements TypeInterface
{
    private readonly TypeInterface $key;

    private readonly TypeInterface $value;

    public function __construct(
        #[TargetTemplateArgument]
        ?TypeInterface $key = null,
        #[TargetTemplateArgument]
        ?TypeInterface $value = null,
    ) {
        [$this->key, $this->value] = match (true) {
            $key === null => [new MixedType(), new MixedType()],
            $value === null => [new MixedType(), $key],
            default => [$key, $value],
        };
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
