<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

use PHPUnit\Framework\Attributes\Before;
use TypeLang\Mapper\Type\Extractor\NativeTypeExtractor;
use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;

trait InteractWithTypeExtractor
{
    protected static ?TypeExtractorInterface $currentTypeExtractor = null;

    #[Before]
    public function beforeInteractWithTypeExtractor(): void
    {
        self::$currentTypeExtractor = null;
    }

    protected static function withTypeExtractor(TypeExtractorInterface $extractor): void
    {
        self::$currentTypeExtractor = $extractor;
    }

    private static function createTypeExtractor(): TypeExtractorInterface
    {
        return new NativeTypeExtractor();
    }

    protected static function getTypeExtractor(): TypeExtractorInterface
    {
        return self::$currentTypeExtractor ??= self::createTypeExtractor();
    }
}
