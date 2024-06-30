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
 * @template TInput of mixed
 * @template TOutput of mixed
 * @template-implements TypeInterface<iterable<array-key, TInput>, list<TOutput>>
 */
final class ListType implements TypeInterface
{
    /**
     * @param TypeInterface<TInput, TOutput> $type
     */
    public function __construct(
        #[TargetTemplateArgument]
        private readonly TypeInterface $type = new MixedType(),
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
            throw InvalidValueException::becauseInvalidValueGiven(
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
     * @throws TypeNotCreatableException
     */
    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): array
    {
        $value = $this->validateAndCast($value, $context);

        $result = [];

        foreach ($value as $index => $item) {
            $context->enter($index);

            $result[] = $this->type->normalize($item, $types, $context);

            $context->leave();
        }

        return $result;
    }

    /**
     * @return list<TInput>
     * @throws InvalidValueException
     * @throws TypeNotCreatableException
     * @throws TypeNotFoundException
     */
    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): array
    {
        $value = $this->validateAndCast($value, $context);

        $result = [];

        foreach ($value as $index => $item) {
            $context->enter($index);

            $result[] = $this->type->denormalize($item, $types, $context);

            $context->leave();
        }

        return $result;
    }
}
