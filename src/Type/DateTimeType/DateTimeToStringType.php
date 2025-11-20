<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\DateTimeType;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\MatchedResult;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TDateTime of \DateTimeInterface = \DateTimeInterface
 *
 * @template-implements TypeInterface<string, TDateTime>
 */
class DateTimeToStringType implements TypeInterface
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_DATETIME_FORMAT = \DateTimeInterface::RFC3339;

    public function __construct(
        /**
         * @var class-string<TDateTime>
         */
        protected readonly string $class,
        /**
         * @var non-empty-string|null
         */
        protected readonly ?string $format = null,
    ) {}

    public function match(mixed $value, RuntimeContext $context): ?MatchedResult
    {
        $expected = \DateTimeInterface::class;

        if ($context->isStrictTypesEnabled()) {
            $expected = $this->class;
        }

        /** @var MatchedResult<TDateTime>|null */
        return MatchedResult::successIf($value, $value instanceof $expected);
    }

    public function cast(mixed $value, RuntimeContext $context): string
    {
        $expected = \DateTimeInterface::class;

        if ($context->isStrictTypesEnabled()) {
            $expected = $this->class;
        }

        if ($value instanceof $expected) {
            return $value->format($this->format ?? self::DEFAULT_DATETIME_FORMAT);
        }

        throw InvalidValueException::createFromContext($context);
    }
}
