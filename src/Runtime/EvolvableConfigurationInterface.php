<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime;

use Psr\Log\LoggerInterface;

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
     * Enables (in case of logger instance is present) or disables
     * (in case of logger is {@see null}) logger.
     */
    public function withLogger(?LoggerInterface $logger = null): self;
}
