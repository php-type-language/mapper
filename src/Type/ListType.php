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
 * @template TInput of mixed
 * @template TOutput of mixed
 * @template-implements TypeInterface<iterable<array-key, TInput>, list<TOutput>>
 */
final class ListType implements TypeInterface
{
    /**
     * @param TypeInterface<TInput, TOutput>|null $type
     */
    public function __construct(
        #[TargetTemplateArgument]
        private readonly ?TypeInterface $type = null,
    ) {}

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

            $result[] = $this->type !== null
                ? $this->type->normalize($item, $types, $context)
                : $this->normalizeImplicitTypedValue($item, $types, $context);

            $context->leave();
        }

        return $result;
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
     * @return list<TInput>
     * @throws InvalidValueException
     * @throws TypeNotFoundException
     */
    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): array
    {
        $value = $this->validateAndCast($value, $context);

        $result = [];

        foreach ($value as $index => $item) {
            $context->enter($index);

            $result[] = $this->type !== null
                ? $this->type->denormalize($item, $types, $context)
                : $this->denormalizeImplicitTypedValue($item, $types, $context);

            $context->leave();
        }

        return $result;
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
