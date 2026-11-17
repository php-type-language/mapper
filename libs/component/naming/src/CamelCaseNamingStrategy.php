<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Naming;

use TypeLang\Mapper\Naming\Internal\Name;
use TypeLang\Mapper\Naming\Internal\WordCase;

/**
 * Lowercase first word, capitalized following words: `userId`.
 */
final class CamelCaseNamingStrategy implements NamingStrategyInterface
{
    public function rename(string $name): string
    {
        /** @var non-empty-string */
        return Name::parse($name)->join('', WordCase::Lower, WordCase::Capitalized);
    }
}
