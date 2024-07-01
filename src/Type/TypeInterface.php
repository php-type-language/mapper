<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Registry\RegistryInterface;

interface TypeInterface
{
    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed;

    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed;
}
