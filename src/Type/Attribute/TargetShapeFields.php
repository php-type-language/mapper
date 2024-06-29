<?php

declare(strict_types=1);

namespace Serafim\Mapper\Type\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class TargetShapeFields extends InjectTarget
{
    public function __construct()
    {
        parent::__construct(Target::ShapeFields);
    }
}
