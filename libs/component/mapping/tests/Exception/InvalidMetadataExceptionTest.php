<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Exception;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\UnknownKeyException;
use TypeLang\Mapper\Mapping\Exception\UnknownPropertyException;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Tests\TestCase;

final class InvalidMetadataExceptionTest extends TestCase
{
    public function testMessageListsErrorWithItsPlace(): void
    {
        $source = new SourceInfo('mapping.json', '$.id');
        $error = UnknownPropertyException::becausePropertyIsNotMapped('stdClass::$id', $source);

        $e = InvalidMetadataException::becauseMetadataIsInvalid(new ClassDraft(\stdClass::class), [$error]);

        self::assertSame(
            "The mapping for class \"stdClass\" contains errors:\n"
                . "  - stdClass::\$id: property is not mapped in mapping.json at \$.id",
            $e->getMessage(),
        );
        self::assertSame([$error], $e->errors);
    }

    public function testMessageListsEveryError(): void
    {
        $errors = [
            UnknownKeyException::becauseKeyIsUnknown('colour', 'stdClass', new SourceInfo()),
            UnknownKeyException::becauseKeyIsUnknown('size', 'stdClass', new SourceInfo('a.php')),
        ];

        $e = InvalidMetadataException::becauseMetadataIsInvalid(new ClassDraft(\stdClass::class), $errors);

        self::assertSame(
            "The mapping for class \"stdClass\" contains errors:\n"
                . "  - stdClass: unknown key \"colour\"\n"
                . '  - stdClass: unknown key "size" in a.php',
            $e->getMessage(),
        );
    }
}
