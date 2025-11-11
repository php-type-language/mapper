<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

trait InteractWithAssertions
{
    protected static function isAssertionsEnabled(): bool
    {
        $enabled = false;

        assert($enabled = true);

        return $enabled;
    }

    protected function skipIfAssertionsDisabled(): void
    {
        if (self::isAssertionsEnabled()) {
            return;
        }

        $this->markTestIncomplete('Assertions must be enabled to run this test');
    }
}
