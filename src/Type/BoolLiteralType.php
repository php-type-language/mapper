<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\Coercer\BoolTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;

class BoolLiteralType extends BoolType
{
    /**
     * @param TypeCoercerInterface<bool> $coercer
     */
    public function __construct(
        protected readonly bool $value,
        TypeCoercerInterface $coercer = new BoolTypeCoercer(),
    ) {
        parent::__construct($coercer);
    }

    #[\Override]
    public function match(mixed $value, Context $context): bool
    {
        return $value === $this->value;
    }

    #[\Override]
    public function cast(mixed $value, Context $context): bool
    {
        // Fast return in case of value if not castable
        if ($value === $this->value) {
            return $value;
        }

        if (!$context->isStrictTypesEnabled()) {
            $coerced = $this->coercer->coerce($value, $context);

            if ($coerced === $this->value) {
                return $coerced;
            }
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
