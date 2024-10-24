<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Context\RootContext;
use TypeLang\Mapper\Runtime\EvolvableConfigurationInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Type\TypeInterface;

final class Mapper implements
    NormalizerInterface,
    DenormalizerInterface,
    EvolvableConfigurationInterface
{
    private readonly TypeRepository $types;

    public function __construct(
        private readonly PlatformInterface $platform = new StandardPlatform(),
        private Configuration $config = new Configuration(),
    ) {
        $this->types = new TypeRepository($this->platform);
    }

    public function withObjectsAsArrays(?bool $enabled = null): self
    {
        $self = clone $this;
        $self->config = $this->config->withObjectsAsArrays($enabled);

        return $self;
    }

    public function withDetailedTypes(?bool $enabled = null): self
    {
        $self = clone $this;
        $self->config = $this->config->withDetailedTypes($enabled);

        return $self;
    }

    /**
     * Returns current mapper platform.
     *
     * @api
     */
    public function getPlatform(): PlatformInterface
    {
        return $this->platform;
    }

    /**
     * Returns current types registry.
     *
     * @api
     */
    public function getTypes(): TypeRepository
    {
        return $this->types;
    }

    /**
     * @throws RuntimeExceptionInterface
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function normalize(mixed $value, ?string $type = null): mixed
    {
        $instance = $this->getType($value, $type);

        return $instance->cast($value, RootContext::forNormalization(
            value: $value,
            config: $this->config,
            types: $this->types,
        ));
    }

    /**
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function isNormalizable(mixed $value, ?string $type = null): bool
    {
        $instance = $this->getType($value, $type);

        return $instance->match($value, RootContext::forNormalization(
            value: $value,
            config: $this->config,
            types: $this->types,
        ));
    }

    /**
     * @throws RuntimeExceptionInterface
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function denormalize(mixed $value, string $type): mixed
    {
        $instance = $this->getType($value, $type);

        return $instance->cast($value, RootContext::forDenormalization(
            value: $value,
            config: $this->config,
            types: $this->types,
        ));
    }

    /**
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function isDenormalizable(mixed $value, string $type): bool
    {
        $instance = $this->getType($value, $type);

        return $instance->match($value, RootContext::forDenormalization(
            value: $value,
            config: $this->config,
            types: $this->types,
        ));
    }

    /**
     * @param non-empty-string|null $type
     *
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    private function getType(mixed $value, ?string $type): TypeInterface
    {
        if ($type === null) {
            return $this->types->getByValue($value);
        }

        return $this->types->getByType($type);
    }
}
