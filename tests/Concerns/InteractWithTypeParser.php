<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

use PHPUnit\Framework\Attributes\Before;
use TypeLang\Mapper\Type\Parser\TypeLangParser;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Parser;

trait InteractWithTypeParser
{
    use InteractWithConcerns;

    protected static ?TypeParserInterface $currentTypeParser = null;

    #[Before]
    public function beforeInteractWithTypeParser(): void
    {
        self::$currentTypeParser = null;
    }

    protected static function withTypeParser(TypeParserInterface $parser): void
    {
        self::$currentTypeParser = $parser;
    }

    private static function createTypeParser(): TypeParserInterface
    {
        if (self::hasConcern(InteractWithPlatform::class)) {
            return TypeLangParser::createFromPlatform(self::getPlatform());
        }

        return new TypeLangParser(new Parser());
    }

    protected static function getTypeParser(): TypeParserInterface
    {
        return self::$currentTypeParser ??= self::createTypeParser();
    }

    /**
     * @throws \Throwable
     */
    protected static function parse(string $stmt): TypeStatement
    {
        $parser = self::getTypeParser();

        return $parser->getStatementByDefinition($stmt);
    }
}
