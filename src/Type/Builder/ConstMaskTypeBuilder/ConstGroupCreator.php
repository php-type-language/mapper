<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\ConstMaskTypeBuilder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\UnionConstType\ConstValuesGroup;

final class ConstGroupCreator
{
    /**
     * @template TArgValue of mixed
     *
     * @param iterable<mixed, TArgValue> $values
     *
     * @return list<ConstValuesGroup<TArgValue>>
     * @throws \Throwable
     */
    public function create(iterable $values, BuildingContext $context): array
    {
        /** @var list<ConstValuesGroup> $groups */
        $groups = [];

        foreach ($values as $value) {
            foreach ($groups as $index => $group) {
                $expectedType = $context->getTypeByValue($value);

                if ($expectedType === $group->type) {
                    $groups[$index] = $group->withAddedValue($value);
                    continue 2;
                }
            }

            $groups[] = new ConstValuesGroup(
                type: $context->getTypeByValue($value),
                values: [$value],
            );
        }

        return $groups;
    }
}
