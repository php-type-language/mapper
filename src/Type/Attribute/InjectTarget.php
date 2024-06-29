<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Attribute;

abstract class InjectTarget
{
    public function __construct(
        public readonly Target $target = Target::TemplateArgument,
    ) {}
}
