<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Type\Coercer\IntTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\Specifier\TypeSpecifierInterface;

/**
 * @template-extends ScalarType<int>
 */
class IntType extends ScalarType
{
    /**
     * @param TypeCoercerInterface<int> $coercer
     * @param TypeSpecifierInterface<int>|null $specifier
     */
    public function __construct(
        TypeCoercerInterface $coercer = new IntTypeCoercer(),
        ?TypeSpecifierInterface $specifier = null,
    ) {
        parent::__construct($coercer, $specifier);
    }

    public function match(mixed $value, Context $context): bool
    {
        return \is_int($value);
    }
}
