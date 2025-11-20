<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\UnitEnumType;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\MatchedResult;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TEnum of \UnitEnum = \UnitEnum
 *
 * @template-implements TypeInterface<non-empty-string, TEnum>
 */
class UnitEnumToStringType implements TypeInterface
{
    public function __construct(
        /**
         * @var class-string<TEnum>
         */
        protected readonly string $class,
    ) {}

    public function match(mixed $value, RuntimeContext $context): ?MatchedResult
    {
        /** @var MatchedResult<TEnum>|null */
        return MatchedResult::successIf($value, $value instanceof $this->class);
    }

    public function cast(mixed $value, RuntimeContext $context): string
    {
        if ($value instanceof $this->class) {
            /** @var non-empty-string */
            return $value->name;
        }

        throw InvalidValueException::createFromContext($context);
    }
}
