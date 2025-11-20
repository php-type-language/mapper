<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\BackedEnumType;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\MatchedResult;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template-covariant TEnum of \BackedEnum = \BackedEnum
 *
 * @template-implements TypeInterface<TEnum, int|string>
 */
class BackedEnumFromScalarType implements TypeInterface
{
    public function __construct(
        /**
         * @var class-string<TEnum>
         */
        protected readonly string $class,
        /**
         * @var TypeInterface<int, int>|TypeInterface<string, string>
         */
        protected readonly TypeInterface $input,
    ) {}

    public function match(mixed $value, RuntimeContext $context): ?MatchedResult
    {
        /** @var MatchedResult<int>|MatchedResult<string>|null $result */
        $result = $this->input->match($value, $context);

        try {
            return $result?->if(($this->class)::tryFrom($result->value) !== null);
        } catch (\Throwable) {
            return null;
        }
    }

    public function cast(mixed $value, RuntimeContext $context): \BackedEnum
    {
        $denormalized = $this->input->cast($value, $context);

        try {
            $case = $this->class::tryFrom($denormalized);
        } catch (\TypeError $e) {
            throw InvalidValueException::createFromContext(
                context: $context,
                previous: $e,
            );
        }

        return $case ?? throw InvalidValueException::createFromContext($context);
    }
}
