<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Type\Coercer\ArrayKeyTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\Specifier\TypeSpecifierInterface;

/**
 * @template-extends ScalarType<array-key>
 */
class ArrayKeyType extends ScalarType
{
    /**
     * @param TypeCoercerInterface<array-key> $coercer
     * @param TypeSpecifierInterface<array-key>|null $specifier
     */
    public function __construct(
        TypeCoercerInterface $coercer = new ArrayKeyTypeCoercer(),
        ?TypeSpecifierInterface $specifier = null,
        /**
         * @var TypeInterface<string>
         */
        protected readonly TypeInterface $string = new StringType(),
        /**
         * @var TypeInterface<int>
         */
        protected readonly TypeInterface $int = new IntType(),
    ) {
        parent::__construct($coercer, $specifier);
    }

    /**
     * @phpstan-assert-if-true array-key $value
     */
    public function match(mixed $value, Context $context): bool
    {
        // TBD (?)
        // It is not entirely clear whether a zero ("0") string
        // key should be allowed, since it is technically
        // impossible to put it in associative array.
        //
        // if ($value === '0') {
        //     return false;
        // }

        return $this->int->match($value, $context)
            || $this->string->match($value, $context);
    }
}
