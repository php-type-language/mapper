<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

/**
 * @template-covariant T of mixed = mixed
 */
final class MatchedResult
{
    private function __construct(
        /**
         * @var T
         */
        public readonly mixed $value,
    ) {}

    /**
     * @return $this|null
     */
    public function if(bool $condition): ?self
    {
        if ($condition) {
            return $this;
        }

        return null;
    }

    /**
     * @template TArg of mixed
     *
     * @param TArg $value
     *
     * @return self<TArg>
     */
    public static function success(mixed $value): self
    {
        return new self($value);
    }

    /**
     * @template TArg of mixed
     *
     * @param TArg $value
     *
     * @return self<TArg>|null
     * @phpstan-return ($condition is true ? self<TArg> : null)
     */
    public static function successIf(mixed $value, bool $condition): ?self
    {
        if ($condition) {
            return new self($value);
        }

        return null;
    }
}
