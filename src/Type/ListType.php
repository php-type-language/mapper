<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\NotInterceptableExceptionInterface;

/**
 * `list<T>`: an array with keys `0, 1, 2, ...` in order, each element of the
 * type. An array with other keys is rejected.
 *
 * @template T
 *
 * @template-implements TypeInterface<list<T>>
 */
final class ListType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<T>
         */
        public readonly TypeInterface $type,
    ) {}

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        $value = $context->coercers->array->coerce($value);

        if ($value === null || !\array_is_list($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (!$this->type->isDenormalizable($item, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<T>
     * @throws InvalidValueException in case of the value is not a list or
     *         an element cannot be denormalized
     */
    public function denormalize(mixed $value, Context $context): array
    {
        $input = $context->coercers->array->coerce($value);

        if ($input === null || !\array_is_list($input)) {
            throw InvalidValueException::becauseInvalidType($value);
        }

        $limit = $context->config->maxViolations;
        $type = $this->type;
        $result = [];
        $violations = [];

        foreach ($input as $index => $item) {
            try {
                $result[] = $type->denormalize($item, $context);
            } catch (NotInterceptableExceptionInterface $e) {
                throw $e;
            } catch (\Exception $e) {
                InvalidValueException::collect(
                    $violations,
                    InvalidValueException::wrapWithPath($e, new KeySegment($index), $item),
                    $limit,
                );
            }
        }

        if ($violations !== []) {
            throw InvalidValueException::becauseViolationsAreCollected($violations);
        }

        return $result;
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        if (!\is_array($value) || !\array_is_list($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (!$this->type->isNormalizable($item, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<mixed>
     * @throws InvalidValueException in case of the value is not a list or
     *         an element cannot be normalized
     */
    public function normalize(mixed $value, Context $context): array
    {
        if (!\is_array($value) || !\array_is_list($value)) {
            throw InvalidValueException::becauseInvalidType($value);
        }

        $type = $this->type;

        $result = [];

        foreach ($value as $index => $item) {
            try {
                $result[] = $type->normalize($item, $context);
            } catch (NotInterceptableExceptionInterface $e) {
                throw $e;
            } catch (\Exception $e) {
                throw InvalidValueException::wrapWithPath($e, new KeySegment($index), $item);
            }
        }

        return $result;
    }
}
