<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\UnionConstType\ConstValuesGroup;

/**
 * @template-covariant TResult of mixed = mixed
 * @template-covariant TMatch of mixed = mixed
 *
 * @template-implements TypeInterface<TResult, TMatch>
 */
final class UnionConstType implements TypeInterface
{
    public function __construct(
        /**
         * @var list<ConstValuesGroup<TResult & TMatch>>
         */
        private readonly array $groups,
    ) {}

    public function match(mixed $value, RuntimeContext $context): ?MatchedResult
    {
        foreach ($this->groups as $group) {
            $result = $group->type->match($value, $context);

            if ($result === null || !\in_array($result->value, $group->values, true)) {
                continue;
            }

            /** @var MatchedResult<TResult & TMatch> */
            return $result;
        }

        return null;
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        $result = $this->match($value, $context);

        if ($result !== null) {
            return $result->value;
        }

        throw InvalidValueException::createFromContext($context);
    }
}
