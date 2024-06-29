<?php

declare(strict_types=1);

namespace Serafim\Mapper\Type;

use Serafim\Mapper\Context;
use Serafim\Mapper\Registry\RegistryInterface;

/**
 * @template-extends NonDirectionalType<mixed, mixed>
 */
final class MixedType extends NonDirectionalType
{
    protected function format(mixed $value, RegistryInterface $types, Context $context): mixed
    {
        return $value;
    }
}
