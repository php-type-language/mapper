<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Tests\Stub;

/**
 * Counts its conversions to a string.
 */
final class StringableStub implements \Stringable
{
    /**
     * @var int<0, max>
     */
    public int $conversions = 0;

    public function __construct(
        private readonly string $text,
    ) {}

    public function __toString(): string
    {
        ++$this->conversions;

        return $this->text;
    }
}
