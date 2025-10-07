<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ConfigReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\EmptyConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\ExpressionConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\NullConditionInfo;

final class SkipConditionsPropertyConfigLoader extends PropertyConfigLoader
{
    public function load(PropertyInfo $info, array $config): void
    {
        if (!isset($config['skip'])) {
            return;
        }

        if (\is_string($config['skip'])) {
            $config['skip'] = [$config['skip']];
        }

        foreach ($config['skip'] as $condition) {
            $info->skip[] = match ($condition) {
                'null' => new NullConditionInfo(),
                'empty' => new EmptyConditionInfo(),
                // TODO Add support of context variable name
                default => new ExpressionConditionInfo(
                    expression: $condition,
                ),
            };
        }
    }
}
