<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\MapName;

final class MultiplePropertiesWithAttributes
{
    #[MapName('field1_alias')]
    public string $field1;

    #[MapName('field2_alias')]
    public string $field2;

    #[MapName('field3_alias')]
    public string $field3;
}
