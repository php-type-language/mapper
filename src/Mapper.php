<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Configuration;
use TypeLang\Mapper\Runtime\Context\Direction;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\Repository\Repository;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;

final class Mapper implements NormalizerInterface, DenormalizerInterface
{
    private readonly RepositoryInterface $types;

    public function __construct(
        private readonly PlatformInterface $platform = new StandardPlatform(),
        private Configuration $config = new Configuration(),
    ) {
        $this->types = new Repository($this->platform);
    }

    /**
     * @api
     *
     * @see Configuration::withObjectsAsArrays()
     */
    public function withObjectsAsArrays(?bool $enabled = null): self
    {
        $self = clone $this;
        $self->config = $this->config->withObjectsAsArrays($enabled);

        return $self;
    }

    /**
     * @api
     *
     * @see Configuration::withDetailedTypes()
     */
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
    public function getTypes(): RepositoryInterface
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

        return $instance->cast($value, new Context(
            direction: Direction::Normalize,
            types: $this->types,
            config: $this->config,
        ));
    }

    /**
     * @throws TypeNotFoundException
     */
    public function isNormalizable(mixed $value, ?string $type = null): bool
    {
        $instance = $this->getType($value, $type);

        return $instance->match($value, new Context(
            direction: Direction::Normalize,
            types: $this->types,
            config: $this->config,
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

        return $instance->cast($value, new Context(
            direction: Direction::Denormalize,
            types: $this->types,
            config: $this->config,
        ));
    }

    /**
     * @throws TypeNotFoundException
     */
    public function isDenormalizable(mixed $value, string $type): bool
    {
        $instance = $this->getType($value, $type);

        return $instance->match($value, new Context(
            direction: Direction::Denormalize,
            types: $this->types,
            config: $this->config,
        ));
    }

    /**
     * @param non-empty-string|null $type
     *
     * @throws TypeNotFoundException
     */
    private function getType(mixed $value, ?string $type): TypeInterface
    {
        if ($type === null) {
            return $this->types->getByValue($value);
        }

        return $this->types->getByType($type);
    }

    private function createLocalContext(Direction $direction): Context
    {
        return new Context(
            direction: $direction,
            types: $this->types,
            config: $this->config,
        );
    }
}
