<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

final class SourceInfoTest extends MetadataTestCase
{
    public function testConstruct(): void
    {
        $info = new SourceInfo(
            file: $file = __FILE__,
            line: $line = __LINE__,
        );

        self::assertSame($file, $info->file);
        self::assertSame($line, $info->line);
    }
}
