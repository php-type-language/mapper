<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Registry\RegistryInterface;

/**
 * @template-extends NonDirectionalType<null>
 */
final class NullType extends NonDirectionalType
{
    protected function format(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        return null;
    }
}
