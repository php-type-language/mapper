<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser\Factory;

use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Parser\InMemoryTypeParser;
use TypeLang\Mapper\Runtime\Parser\LoggableTypeParser;
use TypeLang\Mapper\Runtime\Parser\TraceableTypeParser;
use TypeLang\Mapper\Runtime\Parser\TypeLangParser;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;

/**
 * Implements the basic (built-in) logic for initializing the
 * {@see TypeParserInterface} instance.
 */
final class DefaultTypeParserFactory implements TypeParserFactoryInterface
{
    public function createTypeParser(Configuration $config, PlatformInterface $platform): TypeParserInterface
    {
        $parser = $this->createDefaultParser($platform);

        $parser = $this->withTracing($config, $parser);
        $parser = $this->withLogging($config, $parser);

        return $this->withMemoization($parser);
    }

    private function createDefaultParser(PlatformInterface $platform): TypeLangParser
    {
        return TypeLangParser::createFromPlatform($platform);
    }

    private function withTracing(Configuration $config, TypeParserInterface $parser): TypeParserInterface
    {
        $tracer = $config->getTracer();

        if ($tracer === null) {
            return $parser;
        }

        return new TraceableTypeParser($tracer, $parser);
    }

    private function withLogging(Configuration $config, TypeParserInterface $parser): TypeParserInterface
    {
        $logger = $config->getLogger();

        if ($logger === null) {
            return $parser;
        }

        return new LoggableTypeParser($logger, $parser);
    }

    private function withMemoization(TypeParserInterface $parser): InMemoryTypeParser
    {
        return new InMemoryTypeParser($parser);
    }
}
