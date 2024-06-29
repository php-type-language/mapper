<?php

declare(strict_types=1);

namespace Serafim\Mapper\Type;

use Serafim\Mapper\Context;
use Serafim\Mapper\Context\LocalContext;
use Serafim\Mapper\Exception\Mapping\InvalidValueException;
use Serafim\Mapper\Registry\RegistryInterface;
use Serafim\Mapper\Type\Attribute\TargetTemplateArgument;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * @template T of mixed
 * @template-extends NonDirectionalType<iterable<array-key, T>, list<T>>
 */
final class ListType implements TypeInterface
{
    private bool $isExplicitType;

    /**
     * @param TypeInterface<T> $type
     */
    public function __construct(
        #[TargetTemplateArgument]
        private readonly TypeInterface $type = new MixedType(),
    ) {
        $this->isExplicitType = \func_num_args() > 0;
    }

    private function validateAndCast(mixed $value, LocalContext $context): array
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = match (true) {
                \is_iterable($value) => \iterator_to_array($value),
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

    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): array
    {
        $value = $this->validateAndCast($value, $context);

        $result = [];

        foreach ($value as $index => $item) {
            $context->enter($index);

            $result[] = $this->isExplicitType
                ? $this->normalizeExplicitTypedValue($item, $types, $context)
                : $this->normalizeImplicitTypedValue($item, $types, $context);

            $context->leave();
        }

        return $result;
    }

    /**
     * If the type of the list is not specified, then we try to infer the type
     * based on the value, thus providing a more correct conversion.
     */
    private function normalizeImplicitTypedValue(mixed $value, RegistryInterface $types, Context $context): mixed
    {
        if (\is_object($value) && !$value instanceof \stdClass) {
            $type = $types->get(new NamedTypeNode(
                name: \get_class($value),
            ));

            return $type->normalize($value, $types, $context);
        }

        return $this->normalizeExplicitTypedValue($value, $types, $context);
    }

    /**
     * In the case of the type is specified explicitly, then we use the
     * passed type for conversion.
     */
    private function normalizeExplicitTypedValue(mixed $value, RegistryInterface $types, Context $context): mixed
    {
        return $this->type->normalize($value, $types, $context);
    }

    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): array
    {
        $value = $this->validateAndCast($value, $context);

        $result = [];

        foreach ($value as $index => $item) {
            $context->enter($index);

            $result[] = $this->isExplicitType
                ? $this->denormalizeExplicitTypedValue($item, $types, $context)
                : $this->denormalizeImplicitTypedValue($item, $types, $context);

            $context->leave();
        }

        return $result;
    }

    /**
     * If the type of the list is not specified, then we try to infer the type
     * based on the value, thus providing a more correct conversion.
     */
    private function denormalizeImplicitTypedValue(mixed $value, RegistryInterface $types, Context $context): mixed
    {
        if (\is_object($value) && !$value instanceof \stdClass) {
            $type = $types->get(new NamedTypeNode(
                name: \get_class($value),
            ));

            return $type->denormalize($value, $types, $context);
        }

        return $this->denormalizeExplicitTypedValue($value, $types, $context);
    }

    /**
     * In the case of the type is specified explicitly, then we use the
     * passed type for conversion.
     */
    private function denormalizeExplicitTypedValue(mixed $value, RegistryInterface $types, Context $context): mixed
    {
        return $this->type->denormalize($value, $types, $context);
    }
}
