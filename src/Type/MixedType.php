<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context;
use TypeLang\Mapper\Registry\RegistryInterface;

/**
 * @template-extends NonDirectionalType<mixed>
 */
final class MixedType extends NonDirectionalType
{
    protected function format(mixed $value, RegistryInterface $types, Context $context): mixed
    {
        return $value;
    }
}
