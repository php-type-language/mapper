<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Extractor\Factory;

use TypeLang\Mapper\Context\BootContext;
use TypeLang\Mapper\Type\Extractor\LoggableTypeExtractor;
use TypeLang\Mapper\Type\Extractor\NativeTypeExtractor;
use TypeLang\Mapper\Type\Extractor\TraceableTypeExtractor;
use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;

/**
 * Implements the basic (built-in) logic for initializing the
 * {@see NativeTypeExtractor} instance.
 */
final class DefaultTypeExtractorFactory implements TypeExtractorFactoryInterface
{
    public const DEFAULT_TRACING_OPTION = false;
    public const DEFAULT_LOGGING_OPTION = false;

    public function __construct(
        private readonly bool $enableLogging = self::DEFAULT_LOGGING_OPTION,
        private readonly bool $enableTracing = self::DEFAULT_TRACING_OPTION,
    ) {}

    public function createTypeExtractor(BootContext $context): TypeExtractorInterface
    {
        $extractor = new NativeTypeExtractor();

        if ($this->enableTracing) {
            $extractor = $this->withTracing($extractor, $context);
        }

        if ($this->enableLogging) {
            $extractor = $this->withLogging($extractor, $context);
        }

        return $extractor;
    }

    private function withTracing(TypeExtractorInterface $extractor, BootContext $context): TypeExtractorInterface
    {
        $tracer = $context->config->findTracer();

        if ($tracer === null) {
            return $extractor;
        }

        return new TraceableTypeExtractor($tracer, $extractor);
    }

    private function withLogging(TypeExtractorInterface $extractor, BootContext $context): TypeExtractorInterface
    {
        $logger = $context->config->findLogger();

        if ($logger === null) {
            return $extractor;
        }

        return new LoggableTypeExtractor($logger, $extractor);
    }
}
