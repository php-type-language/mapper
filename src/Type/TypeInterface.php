<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Registry\RegistryInterface;

interface TypeInterface
{
    public function supportsCasting(mixed $value, LocalContext $context): bool;

    public function cast(mixed $value, RegistryInterface $types, LocalContext $context): mixed;
}
