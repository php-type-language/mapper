<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Source;

use TypeLang\Mapper\Mapping\Exception\InvalidDefaultValueException;
use TypeLang\Mapper\Mapping\Metadata\DefaultValueMetadata;

/**
 * Creates default values of the mapping: {@see null}, scalars, enum cases
 * and arrays of them.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class DefaultValueFactory
{
    /**
     * Returns the default value of the property or of its promoted
     * constructor parameter, {@see null} in case of it has none.
     *
     * @throws InvalidDefaultValueException in case of the default value
     *         cannot be evaluated or cannot be a default value of the mapping
     */
    public static function findForProperty(\ReflectionProperty $property, SourceContext $context): ?DefaultValueMetadata
    {
        if (!$property->isPromoted()) {
            if (!$property->hasDefaultValue()) {
                return null;
            }

            return self::createFromValue(self::evaluate($property->getDefaultValue(...), $context), $context);
        }

        $parameter = self::findPromotedParameter($property);

        if ($parameter === null || !$parameter->isDefaultValueAvailable()) {
            return null;
        }

        return self::createFromValue(self::evaluate($parameter->getDefaultValue(...), $context), $context);
    }

    /**
     * @throws InvalidDefaultValueException in case of the value is an object
     *         other than an enum case
     */
    public static function createFromValue(mixed $value, SourceContext $context): DefaultValueMetadata
    {
        $unsupported = self::findUnsupportedType($value);

        if ($unsupported !== null) {
            throw InvalidDefaultValueException::becauseDefaultValueIsNotSupported(
                property: $context->target,
                type: $unsupported,
                source: $context->source,
            );
        }

        return new DefaultValueMetadata($value, $context->source);
    }

    /**
     * @param \Closure(): mixed $evaluate
     * @throws InvalidDefaultValueException in case of the default value
     *         cannot be evaluated
     */
    private static function evaluate(\Closure $evaluate, SourceContext $context): mixed
    {
        try {
            return $evaluate();
        } catch (\Throwable $e) {
            throw InvalidDefaultValueException::becauseDefaultValueCannotBeEvaluated(
                property: $context->target,
                source: $context->source,
                previous: $e,
            );
        }
    }

    private static function findPromotedParameter(\ReflectionProperty $property): ?\ReflectionParameter
    {
        $constructor = $property->getDeclaringClass()->getConstructor();

        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            if ($parameter->name === $property->name) {
                return $parameter;
            }
        }

        return null;
    }

    /**
     * Returns the type of the first value that cannot be a default value,
     * values of arrays included, {@see null} in case of the value can be one.
     *
     * @return non-empty-string|null
     */
    private static function findUnsupportedType(mixed $value): ?string
    {
        if (!\is_array($value)) {
            return self::findUnsupportedItemType($value);
        }

        foreach ($value as $item) {
            $type = self::findUnsupportedType($item);

            if ($type !== null) {
                return $type;
            }
        }

        return null;
    }

    /**
     * @return non-empty-string|null
     */
    private static function findUnsupportedItemType(mixed $value): ?string
    {
        if ($value === null || \is_scalar($value) || $value instanceof \UnitEnum) {
            return null;
        }

        /** @var non-empty-string */
        return \get_debug_type($value);
    }
}
