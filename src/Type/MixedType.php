<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\NotInterceptableExceptionInterface;
use TypeLang\Mapper\Exception\TypeDefinitionException;

/**
 * `mixed`: data is denormalized as is. On normalization an object is
 * normalized by the type of its class from {@see Context::$types}, an array
 * and a {@see \stdClass} element by element, other values are kept as is.
 * A {@see \stdClass} gives an array or an object by the configuration, as
 * an object of a class does.
 *
 * @template-implements TypeInterface<mixed>
 */
final class MixedType implements TypeInterface
{
    public function isDenormalizable(mixed $value, Context $context): bool
    {
        return true;
    }

    public function denormalize(mixed $value, Context $context): mixed
    {
        return $value;
    }

    /**
     * @throws TypeDefinitionException in case of the type of an object
     *         cannot be built
     */
    public function isNormalizable(mixed $value, Context $context): bool
    {
        if (self::isDynamicObject($value)) {
            $value = \get_object_vars($value);
        }

        if (\is_array($value)) {
            foreach ($value as $item) {
                if (!$this->isNormalizable($item, $context)) {
                    return false;
                }
            }

            return true;
        }

        if (!\is_object($value)) {
            return $value === null || \is_scalar($value);
        }

        return $context->types->get($value::class)->isNormalizable($value, $context);
    }

    /**
     * @throws InvalidValueException in case of an element or an object
     *         cannot be normalized
     * @throws TypeDefinitionException in case of the type of an object
     *         cannot be built
     */
    public function normalize(mixed $value, Context $context): mixed
    {
        if ($value === null || \is_scalar($value)) {
            return $value;
        }

        if (self::isDynamicObject($value)) {
            $result = $this->normalize(\get_object_vars($value), $context);

            if ($context->config->normalizeAsArray) {
                return $result;
            }

            return (object) $result;
        }

        if (\is_array($value)) {
            $result = [];

            foreach ($value as $key => $item) {
                if ($item === null || \is_scalar($item)) {
                    $result[$key] = $item;

                    continue;
                }

                try {
                    $result[$key] = $this->normalize($item, $context);
                } catch (NotInterceptableExceptionInterface $e) {
                    throw $e;
                } catch (\Exception $e) {
                    throw InvalidValueException::wrapWithPath($e, new KeySegment($key), $item);
                }
            }

            return $result;
        }

        if (\is_object($value)) {
            return $context->types->get($value::class)->normalize($value, $context);
        }

        throw InvalidValueException::becauseInvalidType($value);
    }

    /**
     * Returns {@see true} for an object of exactly {@see \stdClass}.
     *
     * @phpstan-assert-if-true \stdClass $value
     */
    private static function isDynamicObject(mixed $value): bool
    {
        return \is_object($value) && $value::class === \stdClass::class;
    }
}
