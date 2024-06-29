<?php

declare(strict_types=1);

namespace Serafim\Mapper\Registry;

use Serafim\Mapper\Type\Builder\TypeBuilderInterface;

interface MutableRegistryInterface extends RegistryInterface
{
    public function append(TypeBuilderInterface $type): void;

    public function prepend(TypeBuilderInterface $type): void;
}
