<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Check;

/**
 * Float within the bounds, both inclusive. A {@see \NAN} is out of any
 * bounds.
 *
 * ```
 * // positive-float: the lowest value above the zero
 * new FloatRangeCheck(4.9E-324, \INF, '> 0');
 * ```
 */
final class FloatRangeCheck implements CheckInterface
{
    public function __construct(
        public readonly float $min,
        public readonly float $max,
        /**
         * Bounds as the message describes them: `> 0`, `finite`
         *
         * @var non-empty-string
         */
        public readonly string $description,
    ) {}

    public function isValid(mixed $value): bool
    {
        return \is_float($value) && $value >= $this->min && $value <= $this->max;
    }
}
