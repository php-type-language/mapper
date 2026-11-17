<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Naming;

use TypeLang\Mapper\Naming\Internal\Name;
use TypeLang\Mapper\Naming\Internal\WordCase;

/**
 * Capitalized words: `UserId`.
 */
final class PascalCaseNamingStrategy implements NamingStrategyInterface
{
    public function rename(string $name): string
    {
        /** @var non-empty-string */
        return Name::parse($name)->join('', WordCase::Capitalized, WordCase::Capitalized);
    }
}
