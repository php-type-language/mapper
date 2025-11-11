<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\Validator\Traverser;

use TypeLang\Mapper\Type\Builder\Validator\GroupedRuleInterface;
use TypeLang\Mapper\Type\Builder\Validator\RuleInterface;

final class RuleCollector
{
    /**
     * @param iterable<mixed, RuleInterface> $rules
     * @return list<RuleInterface>
     */
    public static function collect(iterable $rules): array
    {
        $result = [];

        foreach ($rules as $rule) {
            if ($rule instanceof GroupedRuleInterface) {
                $result[$rule->getGroup()] = $rule;
            } else {
                $result[] = $rule;
            }
        }

        return \array_values($result);
    }
}
