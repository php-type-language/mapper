<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\Attribute\TargetTemplateArgument;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * @template TInputKey of array-key
 * @template TOutputKey of array-key
 * @template TInput of mixed
 * @template TOutput of mixed
 * @template-implements TypeInterface<iterable<TInputKey, TInput>, array<TOutputKey, TOutput>>
 */
final class ArrayType implements TypeInterface
{
    private readonly bool $allowIntKeys;

    /**
     * @var TypeInterface<TInputKey, TOutputKey>
     */
    private readonly TypeInterface $key;

    /**
     * @var TypeInterface<TInput, TOutput>|null
     */
    private readonly ?TypeInterface $value;

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
        $this->allowIntKeys = $value === null;

        [$this->key, $this->value] = match (true) {
            $key === null => [new StringType(), null],
            $value === null => [new StringType(), $key],
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
     * @throws TypeNotFoundException
     */
    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): array
    {
        $value = $this->validateAndCast($value, $context);

        $result = [];

        foreach ($value as $index => $item) {
            $context->enter($index);

            $result[$this->normalizeKey($index, $types, $context)] = $this->value !== null
                ? $this->value->normalize($item, $types, $context)
                : $this->normalizeImplicitTypedValue($item, $types, $context);

            $context->leave();
        }

        return $result;
    }

    private function normalizeKey(mixed $index, RegistryInterface $types, LocalContext $context): int|string
    {
        if ($this->allowIntKeys && \is_int($index)) {
            return $index;
        }

        return $this->key->normalize($index, $types, $context);
    }

    /**
     * If the type of the list is not specified, then we try to infer the type
     * based on the value, thus providing a more correct conversion.
     *
     * @throws TypeNotFoundException
     */
    private function normalizeImplicitTypedValue(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        if (\is_object($value) && !$value instanceof \stdClass) {
            $type = $types->get(new NamedTypeNode(
                name: \get_class($value),
            ));

            return $type->normalize($value, $types, $context);
        }

        return $value;
    }

    /**
     * @return array<TInputKey, TInput>
     * @throws InvalidValueException
     * @throws TypeNotFoundException
     */
    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): array
    {
        $value = $this->validateAndCast($value, $context);

        $result = [];

        foreach ($value as $index => $item) {
            $context->enter($index);

            $result[$this->denormalizeKey($index, $types, $context)] = $this->value !== null
                ? $this->value->denormalize($item, $types, $context)
                : $this->denormalizeImplicitTypedValue($item, $types, $context);

            $context->leave();
        }

        return $result;
    }

    private function denormalizeKey(mixed $index, RegistryInterface $types, LocalContext $context): int|string
    {
        if ($this->allowIntKeys && \is_int($index)) {
            return $index;
        }

        return $this->key->denormalize($index, $types, $context);
    }

    /**
     * If the type of the list is not specified, then we try to infer the type
     * based on the value, thus providing a more correct conversion.
     *
     * @throws TypeNotFoundException
     */
    private function denormalizeImplicitTypedValue(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        if (\is_object($value) && !$value instanceof \stdClass) {
            $type = $types->get(new NamedTypeNode(
                name: \get_class($value),
            ));

            return $type->denormalize($value, $types, $context);
        }

        return $value;
    }
}
