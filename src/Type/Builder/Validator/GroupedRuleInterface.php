<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\Validator;

interface GroupedRuleInterface extends RuleInterface
{
    /**
     * @return non-empty-string
     */
    public function getGroup(): string;
}
