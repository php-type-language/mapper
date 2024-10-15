<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Context\Context;
use TypeLang\Mapper\Runtime\Context\Direction;
use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Mapper\Type\Repository\Repository;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;

final class Mapper implements NormalizerInterface, DenormalizerInterface
{
    private readonly RepositoryInterface $types;

    public function __construct(
        private readonly PlatformInterface $platform = new StandardPlatform(),
        private Context $context = new Context(),
    ) {
        $this->types = new Repository($this->platform);
    }

    /**
     * @api
     *
     * @see Context::withObjectsAsArrays()
     */
    public function withObjectsAsArrays(?bool $enabled = null): self
    {
        $self = clone $this;
        $self->context = $this->context->withObjectsAsArrays($enabled);

        return $self;
    }

    /**
     * @api
     *
     * @see Context::withDetailedTypes()
     */
    public function withDetailedTypes(?bool $enabled = null): self
    {
        $self = clone $this;
        $self->context = $this->context->withDetailedTypes($enabled);

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
     * @throws TypeNotFoundException
     */
    public function normalize(mixed $value, ?string $type = null): mixed
    {
        $instance = $this->getType($value, $type);

        $local = $this->createLocalContext(Direction::Normalize);

        return $instance->cast($value, $local);
    }

    /**
     * @throws TypeNotFoundException
     */
    public function isNormalizable(mixed $value, ?string $type = null): bool
    {
        $instance = $this->getType($value, $type);

        $local = $this->createLocalContext(Direction::Normalize);

        return $instance->match($value, $local);
    }

    /**
     * @throws TypeNotFoundException
     */
    public function denormalize(mixed $value, string $type): mixed
    {
        $instance = $this->getType($value, $type);

        $local = $this->createLocalContext(Direction::Denormalize);

        return $instance->cast($value, $local);
    }

    /**
     * @throws TypeNotFoundException
     */
    public function isDenormalizable(mixed $value, string $type): bool
    {
        $instance = $this->getType($value, $type);

        $local = $this->createLocalContext(Direction::Denormalize);

        return $instance->match($value, $local);
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

    private function createLocalContext(Direction $direction): LocalContext
    {
        return new LocalContext(
            direction: $direction,
            types: $this->types,
            objectsAsArrays: $this->context->isObjectsAsArrays(),
            detailedTypes: $this->context->isDetailedTypes(),
        );
    }
}
