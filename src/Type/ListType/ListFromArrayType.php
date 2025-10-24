<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ListType;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;

/**
 * @template-covariant TItem of mixed = mixed
 * @template-extends ListFromIterableType<TItem>
 */
class ListFromArrayType extends ListFromIterableType
{
    /**
     * @phpstan-assert-if-true array<array-key, mixed> $value
     */
    #[\Override]
    public function match(mixed $value, Context $context): bool
    {
        if ($context->isStrictTypesEnabled()) {
            return \is_array($value) && \array_is_list($value);
        }

        return \is_array($value);
    }

    #[\Override]
    public function cast(mixed $value, Context $context): array
    {
        if (!$this->match($value, $context)) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        return $this->process($value, $context);
    }
}
