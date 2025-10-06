<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\EmptyConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\ExpressionConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\NullConditionInfo;
use TypeLang\Mapper\Mapping\SkipWhen;
use TypeLang\Mapper\Mapping\SkipWhenEmpty;
use TypeLang\Mapper\Mapping\SkipWhenNull;

final class SkipConditionsPropertyAttributeLoader extends PropertyAttributeLoader
{
    public function load(\ReflectionProperty $property, PropertyInfo $prototype): void
    {
        $this->loadUserConditions($property, $prototype);
        $this->loadNullCondition($property, $prototype);
        $this->loadEmptyCondition($property, $prototype);
    }

    private function loadUserConditions(\ReflectionProperty $property, PropertyInfo $prototype): void
    {
        $conditions = $this->getAllPropertyAttributes($property, SkipWhen::class);

        foreach ($conditions as $condition) {
            $prototype->skip[] = new ExpressionConditionInfo(
                expression: $condition->expr,
                context: $condition->context,
            );
        }
    }

    private function loadNullCondition(\ReflectionProperty $property, PropertyInfo $prototype): void
    {
        $condition = $this->findPropertyAttribute($property, SkipWhenNull::class);

        if ($condition === null) {
            return;
        }

        $prototype->skip[] = new NullConditionInfo();
    }

    private function loadEmptyCondition(\ReflectionProperty $property, PropertyInfo $prototype): void
    {
        $condition = $this->findPropertyAttribute($property, SkipWhenEmpty::class);

        if ($condition === null) {
            return;
        }

        $prototype->skip[] = new EmptyConditionInfo();
    }
}
