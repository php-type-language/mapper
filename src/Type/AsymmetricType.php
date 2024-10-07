<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;

abstract class AsymmetricType implements TypeInterface
{
    public function cast(mixed $value, RepositoryInterface $types, LocalContext $context): mixed
    {
        if ($context->isDenormalization()) {
            return $this->denormalize($value, $types, $context);
        }

        return $this->normalize($value, $types, $context);
    }

    abstract protected function normalize(mixed $value, RepositoryInterface $types, LocalContext $context): mixed;

    abstract protected function denormalize(mixed $value, RepositoryInterface $types, LocalContext $context): mixed;
}
