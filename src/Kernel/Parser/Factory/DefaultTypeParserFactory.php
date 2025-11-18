<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Parser\Factory;

use TypeLang\Mapper\Context\BootContext;
use TypeLang\Mapper\Kernel\Parser\InMemoryTypeParser;
use TypeLang\Mapper\Kernel\Parser\LoggableTypeParser;
use TypeLang\Mapper\Kernel\Parser\TraceableTypeParser;
use TypeLang\Mapper\Kernel\Parser\TypeLangParser;
use TypeLang\Mapper\Kernel\Parser\TypeParserInterface;

/**
 * Implements the basic (built-in) logic for initializing the
 * {@see TypeParserInterface} instance.
 */
final class DefaultTypeParserFactory implements TypeParserFactoryInterface
{
    public const DEFAULT_TRACING_OPTION = false;
    public const DEFAULT_LOGGING_OPTION = false;
    public const DEFAULT_MEMOIZATION_OPTION = true;

    /**
     * @var int<0, max>
     */
    public const DEFAULT_MAX_IN_MEMORY_TYPES = InMemoryTypeParser::DEFAULT_MAX_IN_MEMORY_TYPES;

    /**
     * @var int<0, max>
     */
    public const DEFAULT_MIN_IN_MEMORY_TYPES = InMemoryTypeParser::DEFAULT_MIN_IN_MEMORY_TYPES;

    public function __construct(
        private readonly bool $enableLogging = self::DEFAULT_LOGGING_OPTION,
        private readonly bool $enableTracing = self::DEFAULT_TRACING_OPTION,
        private readonly bool $enableMemoization = self::DEFAULT_MEMOIZATION_OPTION,
        /**
         * @var int<0, max>
         */
        private readonly int $maxTypesInMemory = self::DEFAULT_MAX_IN_MEMORY_TYPES,
        /**
         * @var int<0, max>
         */
        private readonly int $minTypesInMemory = self::DEFAULT_MIN_IN_MEMORY_TYPES,
    ) {}

    public function createTypeParser(BootContext $context): TypeParserInterface
    {
        $parser = TypeLangParser::createFromPlatform(
            platform: $context->platform,
        );

        if ($this->enableTracing) {
            $parser = $this->withTracing($parser, $context);
        }

        if ($this->enableLogging) {
            $parser = $this->withLogging($parser, $context);
        }

        if ($this->enableMemoization) {
            $parser = $this->withMemoization($parser);
        }

        return $parser;
    }

    private function withTracing(TypeParserInterface $parser, BootContext $context): TypeParserInterface
    {
        $tracer = $context->config->findTracer();

        if ($tracer === null) {
            return $parser;
        }

        return new TraceableTypeParser($tracer, $parser);
    }

    private function withLogging(TypeParserInterface $parser, BootContext $context): TypeParserInterface
    {
        $logger = $context->config->findLogger();

        if ($logger === null) {
            return $parser;
        }

        return new LoggableTypeParser($logger, $parser);
    }

    private function withMemoization(TypeParserInterface $parser): TypeParserInterface
    {
        return new InMemoryTypeParser(
            delegate: $parser,
            maxTypesLimit: $this->maxTypesInMemory,
            minTypesLimit: $this->minTypesInMemory,
        );
    }
}
