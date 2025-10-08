<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\OnTypeError;
use TypeLang\Mapper\Mapping\OnUndefinedError;

final class PropertyWithErrorMessages
{
    #[OnTypeError('Type error for field')]
    #[OnUndefinedError('Field is required')]
    public int $field;
}
