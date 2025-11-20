<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ObjectType;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Type\MatchedResult;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template-implements TypeInterface<object, object|array<array-key, mixed>>
 */
class ObjectFromArrayType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<array<array-key, mixed>, array<array-key, mixed>>
         */
        protected readonly TypeInterface $input,
    ) {}

    public function match(mixed $value, RuntimeContext $context): ?MatchedResult
    {
        if (\is_object($value)) {
            return MatchedResult::success($value);
        }

        return $this->input->match($value, $context);
    }

    public function cast(mixed $value, RuntimeContext $context): object
    {
        if (\is_object($value)) {
            return $value;
        }

        return (object) $this->input->cast($value, $context);
    }
}
