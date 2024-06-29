<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\TypeNotCreatableException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\Attribute\TargetTemplateArgument;

/**
 * @template TInputKey of array-key
 * @template TOutputKey of array-key
 * @template TInput of mixed
 * @template TOutput of mixed
 * @template-implements TypeInterface<iterable<TInputKey, TInput>, array<TOutputKey, TOutput>>
 */
final class ArrayType implements TypeInterface
{
    /**
     * @var TypeInterface<TInputKey, TOutputKey>
     */
    private readonly TypeInterface $key;

    /**
     * @var TypeInterface<TInput, TOutput>
     */
    private readonly TypeInterface $value;

    /**
     * @param TypeInterface<TInputKey, TOutputKey>|TypeInterface<TInput, TOutput>|null $key
     * @param TypeInterface<TInput, TOutput>|null $value
     */
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
            throw InvalidValueException::becauseInvalidValue(
                context: $context,
                expectedType: 'array',
                actualValue: $value,
            );
        }

        return $value;
    }

    /**
     * @throws InvalidValueException
     * @throws TypeNotCreatableException
     * @throws TypeNotFoundException
     */
    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): array
    {
        $value = $this->validateAndCast($value, $context);

        $result = [];

        foreach ($value as $index => $item) {
            $context->enter($index);

            $result[$this->key->normalize($index, $types, $context)]
                = $this->value->normalize($item, $types, $context);

            $context->leave();
        }

        return $result;
    }

    /**
     * @return array<TInputKey, TInput>
     * @throws InvalidValueException
     * @throws TypeNotFoundException
     * @throws TypeNotCreatableException
     */
    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): array
    {
        $value = $this->validateAndCast($value, $context);

        $result = [];

        foreach ($value as $index => $item) {
            $context->enter($index);

            $result[$this->key->denormalize($index, $types, $context)]
                = $this->value->denormalize($item, $types, $context);

            $context->leave();
        }

        return $result;
    }
}
