<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader\PhpDocMetadataReader;

use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader\MapperPlatform;
use TypeLang\Mapper\Mapping\Tests\TestCase;

final class MapperPlatformTest extends TestCase
{
    public function testDefaultTagNamesHaveAnalyzerAliasesOfTheirTag(): void
    {
        $aliases = [];

        foreach (MapperPlatform::createWithDefaultTagNames()->getAliases() as $alias => $tag) {
            $aliases[$alias] = $tag;
        }

        self::assertSame([
            'phpstan-var' => 'var',
            'psalm-var' => 'var',
            'phan-var' => 'var',
            'phpstan-param' => 'param',
            'psalm-param' => 'param',
            'phan-param' => 'param',
            'phpstan-return' => 'return',
            'psalm-return' => 'return',
            'phan-return' => 'return',
        ], $aliases);
    }
}
