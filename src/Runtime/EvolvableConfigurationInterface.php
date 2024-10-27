<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Runtime\Tracing\TracerInterface;

interface EvolvableConfigurationInterface
{
    /**
     * Enables or disables object to arrays conversion.
     *
     * In case of $enabled is {@see null} a default value will be defined.
     */
    public function withObjectsAsArrays(?bool $enabled = null): self;

    /**
     * Enables or disables detailed types in exceptions.
     *
     * In case of $enabled is {@see null} a default value will be defined.
     */
    public function withDetailedTypes(?bool $enabled = null): self;

    /**
     * Enables logging using passed instance in case of {@see LoggerInterface}
     * instance is present or disables it in case of logger is {@see null}.
     */
    public function withLogger(?LoggerInterface $logger = null): self;

    /**
     * Enables application tracing using passed instance in case of
     * {@see TracerInterface} instance is present or disables it in case of
     * tracer is {@see null}.
     */
    public function withTracer(?TracerInterface $tracer = null): self;
}
