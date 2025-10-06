<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\AttributeReader;

use TypeLang\Mapper\Mapping\Metadata\Condition\EmptyConditionPrototype;
use TypeLang\Mapper\Mapping\Metadata\Condition\ExpressionConditionPrototype;
use TypeLang\Mapper\Mapping\Metadata\Condition\NullConditionPrototype;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyPrototype;
use TypeLang\Mapper\Mapping\SkipWhen;
use TypeLang\Mapper\Mapping\SkipWhenEmpty;
use TypeLang\Mapper\Mapping\SkipWhenNull;

final class SkipConditionsPropertyAttributeLoader extends PropertyAttributeLoader
{
    public function load(\ReflectionProperty $property, PropertyPrototype $prototype): void
    {
        $this->loadUserConditions($property, $prototype);
        $this->loadNullCondition($property, $prototype);
        $this->loadEmptyCondition($property, $prototype);
    }

    private function loadUserConditions(\ReflectionProperty $property, PropertyPrototype $prototype): void
    {
        $conditions = $this->getAllPropertyAttributes($property, SkipWhen::class);

        foreach ($conditions as $condition) {
            $prototype->skip->add(new ExpressionConditionPrototype(
                expression: $condition->expr,
                context: $condition->context,
            ));
        }
    }

    private function loadNullCondition(\ReflectionProperty $property, PropertyPrototype $prototype): void
    {
        $condition = $this->findPropertyAttribute($property, SkipWhenNull::class);

        if ($condition === null) {
            return;
        }

        $prototype->skip->add(new NullConditionPrototype());
    }

    private function loadEmptyCondition(\ReflectionProperty $property, PropertyPrototype $prototype): void
    {
        $condition = $this->findPropertyAttribute($property, SkipWhenEmpty::class);

        if ($condition === null) {
            return;
        }

        $prototype->skip->add(new EmptyConditionPrototype());
    }
}
