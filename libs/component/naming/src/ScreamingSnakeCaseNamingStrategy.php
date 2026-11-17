<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Naming;

use TypeLang\Mapper\Naming\Internal\Name;
use TypeLang\Mapper\Naming\Internal\WordCase;

/**
 * Uppercase words joined by underscore: `USER_ID`.
 */
final class ScreamingSnakeCaseNamingStrategy implements NamingStrategyInterface
{
    public function rename(string $name): string
    {
        /** @var non-empty-string */
        return Name::parse($name)->join('_', WordCase::Upper, WordCase::Upper);
    }
}
