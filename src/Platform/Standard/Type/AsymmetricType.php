<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Type;

use TypeLang\Mapper\Runtime\Context;

/**
 * @template TNormalization of TypeInterface
 * @template TDenormalization of TypeInterface
 */
class AsymmetricType implements TypeInterface
{
    /**
     * @param TNormalization $normalizer
     * @param TDenormalization $denormalizer
     */
    public function __construct(
        protected readonly TypeInterface $normalizer,
        protected readonly TypeInterface $denormalizer,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        if ($context->isDenormalization()) {
            return $this->denormalizer->match($value, $context);
        }

        return $this->normalizer->match($value, $context);
    }

    public function cast(mixed $value, Context $context): mixed
    {
        if ($context->isDenormalization()) {
            return $this->denormalizer->cast($value, $context);
        }

        return $this->normalizer->cast($value, $context);
    }
}
