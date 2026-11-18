<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * Wrapper of a type no default expander supports.
 *
 * @template-implements TypeInterface<mixed>
 */
final class OpaqueTypeStub implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<mixed>
         */
        private readonly TypeInterface $type,
    ) {}

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        return $this->type->isDenormalizable($value, $context);
    }

    public function denormalize(mixed $value, Context $context): mixed
    {
        return $this->type->denormalize($value, $context);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return $this->type->isNormalizable($value, $context);
    }

    public function normalize(mixed $value, Context $context): mixed
    {
        return $this->type->normalize($value, $context);
    }
}
