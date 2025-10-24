<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Coercer\StringTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

/**
 * @template-implements TypeInterface<string>
 */
class StringType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeCoercerInterface<string>
         */
        protected readonly TypeCoercerInterface $coercer = new StringTypeCoercer(),
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return \is_string($value);
    }

    public function cast(mixed $value, Context $context): string
    {
        return match (true) {
            \is_string($value) => $value,
            !$context->isStrictTypesEnabled() => $this->coercer->coerce($value, $context),
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }
}
