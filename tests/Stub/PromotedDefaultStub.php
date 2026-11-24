<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

/**
 * Promoted properties that are not written: only read, not mapped. A setter
 * failing with an error.
 */
final class PromotedDefaultStub
{
    public function __construct(
        #[MapGetter]
        public string $name = 'default',
        private string $secret = 'hidden',
    ) {}

    public function getSecret(): string
    {
        return $this->secret;
    }

    #[MapSetter('times')]
    public function setTimes(int $times = 0): void
    {
        \str_repeat('a', $times);
    }
}
