<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\Context\LocalContext;

abstract class AsymmetricLogicalType extends AsymmetricType implements LogicalTypeInterface
{
    public function supportsCasting(mixed $value, LocalContext $context): bool
    {
        if ($context->isDenormalization()) {
            return $this->supportsDenormalization($value, $context);
        }

        return $this->supportsNormalization($value, $context);
    }

    abstract protected function supportsNormalization(mixed $value, LocalContext $context): bool;

    abstract protected function supportsDenormalization(mixed $value, LocalContext $context): bool;
}
