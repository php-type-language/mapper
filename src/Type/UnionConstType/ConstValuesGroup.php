<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\UnionConstType;

use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template T of mixed = mixed
 */
final class ConstValuesGroup
{
    public function __construct(
        /**
         * @var TypeInterface<T, T>
         */
        public readonly TypeInterface $type,
        /**
         * @var list<T>
         */
        public readonly array $values,
    ) {}

    /**
     * @template TArg of T
     *
     * @param TArg $value
     *
     * @return self<TArg>
     */
    public function withAddedValue(mixed $value): self
    {
        /** @var self<TArg> */
        return new self($this->type, [...$this->values, $value]);
    }
}
