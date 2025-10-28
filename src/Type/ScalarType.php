<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\Specifier\TypeSpecifierInterface;

/**
 * @template-covariant TResult of mixed = mixed
 *
 * @template-implements TypeInterface<TResult>
 *
 * @phpstan-consistent-constructor
 */
abstract class ScalarType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeCoercerInterface<TResult>
         */
        protected readonly TypeCoercerInterface $coercer,
        /**
         * @var TypeSpecifierInterface<TResult>|null
         */
        protected readonly ?TypeSpecifierInterface $specifier = null,
    ) {}

    public function cast(mixed $value, Context $context): mixed
    {
        $original = $value;

        if (!$context->isStrictTypesEnabled()) {
            $value = $this->coercer->coerce($value, $context);
        }

        if (!$this->match($value, $context)) {
            throw InvalidValueException::createFromContext(
                value: $original,
                context: $context,
            );
        }

        $isTypeSpecifiersPassed = !$context->isTypeSpecifiersEnabled()
            || $this->specifier?->match($value, $context) !== false;

        if ($isTypeSpecifiersPassed) {
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $original,
            context: $context,
        );
    }
}
