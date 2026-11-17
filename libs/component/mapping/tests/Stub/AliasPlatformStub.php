<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\PhpDoc\Platform\PlatformInterface;

/**
 * Platform declaring the given tag names as aliases of standard tags:
 * `['type-lang-var' => 'var']`.
 */
final class AliasPlatformStub implements PlatformInterface
{
    public function __construct(
        /**
         * @var array<non-empty-lowercase-string, non-empty-lowercase-string>
         */
        private readonly array $aliases,
    ) {}

    public function getTags(): iterable
    {
        return [];
    }

    public function getAliases(): iterable
    {
        return $this->aliases;
    }

    public function getCombinators(): iterable
    {
        return [];
    }
}
