<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\Context\LocalContext;

interface LogicalTypeInterface extends TypeInterface
{
    public function supportsCasting(mixed $value, LocalContext $context): bool;
}
