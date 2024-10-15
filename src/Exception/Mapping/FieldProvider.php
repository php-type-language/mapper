<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

/**
 * @internal this is an internal library trait, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Exception\Mapping
 *
 * @phpstan-require-implements FieldExceptionInterface
 *
 * @mixin FieldExceptionInterface
 */
trait FieldProvider
{
    /**
     * @var non-empty-string
     */
    protected readonly string $field;

    public function getField(): string
    {
        return $this->field;
    }
}
