<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Coercer\ArrayCoercer;
use TypeLang\Mapper\Coercer\BoolCoercer;
use TypeLang\Mapper\Coercer\CoercerInterface;
use TypeLang\Mapper\Coercer\FloatCoercer;
use TypeLang\Mapper\Coercer\IntCoercer;
use TypeLang\Mapper\Coercer\StringCoercer;

/**
 * Coercion rules of the mapper: how a value written in another form becomes
 * a value of the type, such as `'42'` of `int`. Used by the scalar types
 * unless the context is strict, the composite types read their input by
 * {@see self::$array} in any mode.
 *
 * ```
 * new Mapper(coercers: new Coercers(float: new DecimalCommaCoercer()));
 * ```
 */
final class Coercers
{
    public function __construct(
        /**
         * @var CoercerInterface<int>
         */
        public readonly CoercerInterface $int = new IntCoercer(),
        /**
         * @var CoercerInterface<float>
         */
        public readonly CoercerInterface $float = new FloatCoercer(),
        /**
         * @var CoercerInterface<string>
         */
        public readonly CoercerInterface $string = new StringCoercer(),
        /**
         * @var CoercerInterface<bool>
         */
        public readonly CoercerInterface $bool = new BoolCoercer(),
        /**
         * Input of the arrays, the lists and the objects
         *
         * @var CoercerInterface<array<array-key, mixed>>
         */
        public readonly CoercerInterface $array = new ArrayCoercer(),
    ) {}
}
