<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Check;

/**
 * String is an integer written in its canonical decimal form, the one PHP
 * turns into an integer key of an array: `'42'` and `'-5'`, but not `'042'`,
 * `'+5'` or `'1.0'`. Or the opposite: any string but such an integer.
 */
final class DecimalIntCheck implements CheckInterface
{
    /**
     * @var non-empty-string
     */
    public readonly string $description;

    public function __construct(
        /**
         * Whether the string must be such an integer or must not be one
         */
        public readonly bool $isDecimal = true,
    ) {
        $description = 'non-decimal-int';

        if ($isDecimal) {
            $description = 'decimal-int';
        }

        $this->description = $description;
    }

    public function isValid(mixed $value): bool
    {
        return \is_string($value) && ((string) (int) $value === $value) === $this->isDecimal;
    }
}
