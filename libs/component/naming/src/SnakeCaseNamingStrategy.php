<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Naming;

use TypeLang\Mapper\Naming\Internal\Name;
use TypeLang\Mapper\Naming\Internal\WordCase;

/**
 * Lowercase words joined by underscore: `user_id`.
 */
final class SnakeCaseNamingStrategy implements NamingStrategyInterface
{
    public function rename(string $name): string
    {
        /** @var non-empty-string */
        return Name::parse($name)->join('_', WordCase::Lower, WordCase::Lower);
    }
}
