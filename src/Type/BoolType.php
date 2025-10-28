<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Type\Coercer\BoolTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\Specifier\TypeSpecifierInterface;

/**
 * @template-extends ScalarType<bool>
 */
class BoolType extends ScalarType
{
    /**
     * @param TypeCoercerInterface<bool> $coercer
     * @param TypeSpecifierInterface<bool>|null $specifier
     */
    public function __construct(
        TypeCoercerInterface $coercer = new BoolTypeCoercer(),
        ?TypeSpecifierInterface $specifier = null,
    ) {
        parent::__construct($coercer, $specifier);
    }

    public function match(mixed $value, Context $context): bool
    {
        return \is_bool($value);
    }
}
