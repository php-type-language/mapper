<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Type\Coercer\FloatTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\Specifier\TypeSpecifierInterface;

/**
 * @template-extends ScalarType<float>
 */
class FloatType extends ScalarType
{
    /**
     * @param TypeCoercerInterface<float> $coercer
     * @param TypeSpecifierInterface<float>|null $specifier
     */
    public function __construct(
        TypeCoercerInterface $coercer = new FloatTypeCoercer(),
        ?TypeSpecifierInterface $specifier = null,
    ) {
        parent::__construct($coercer, $specifier);
    }

    public function match(mixed $value, Context $context): bool
    {
        return \is_float($value);
    }
}
