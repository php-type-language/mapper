<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\Validator;

abstract class TemplateArgumentHintsRule extends TemplateArgumentRule
{
    #[\Override]
    public function getGroup(): string
    {
        return 'template-arg-hint';
    }
}
