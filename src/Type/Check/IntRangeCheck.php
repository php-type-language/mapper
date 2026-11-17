<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Check;

/**
 * Integer within the bounds, both inclusive.
 */
final class IntRangeCheck implements CheckInterface
{
    /**
     * Bounds as the message describes them: `>= 1`, `<= -1`,
     * `between 1 and 10`
     *
     * @var non-empty-string
     */
    public readonly string $description;

    public function __construct(
        /**
         * Lowest allowed value, {@see null} in case of no lower bound
         */
        public readonly ?int $min = null,
        /**
         * Highest allowed value, {@see null} in case of no upper bound
         */
        public readonly ?int $max = null,
    ) {
        $this->description = self::describe($min, $max);
    }

    public function isValid(mixed $value): bool
    {
        return \is_int($value)
            && ($this->min === null || $value >= $this->min)
            && ($this->max === null || $value <= $this->max);
    }

    /**
     * @return non-empty-string
     */
    private static function describe(?int $min, ?int $max): string
    {
        if ($min !== null && $max !== null) {
            return \sprintf('between %d and %d', $min, $max);
        }

        if ($min !== null) {
            return \sprintf('>= %d', $min);
        }

        if ($max !== null) {
            return \sprintf('<= %d', $max);
        }

        return 'any';
    }
}
