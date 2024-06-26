<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class TargetTypeName extends InjectTarget
{
    public function __construct()
    {
        parent::__construct(Target::TypeName);
    }
}
