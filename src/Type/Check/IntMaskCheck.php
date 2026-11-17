<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Check;

/**
 * Integer has no bits besides the bits of the mask, a zero has none.
 */
final class IntMaskCheck implements CheckInterface
{
    /**
     * @var non-empty-string
     */
    public readonly string $description;

    public function __construct(
        /**
         * All allowed bits
         *
         * @var int<0, max>
         */
        public readonly int $mask,
    ) {
        $this->description = \sprintf('bits of %d', $mask);
    }

    public function isValid(mixed $value): bool
    {
        return \is_int($value) && ($value & ~$this->mask) === 0;
    }
}
