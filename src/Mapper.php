<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Type\Context\Context;
use TypeLang\Mapper\Type\Context\Direction;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Mapper\Type\Repository\Repository;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;

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
    public function normalize(mixed $value, ?string $type = null, ?Context $context = null): mixed
    {
        $concreteType = $type === null
            ? $this->types->getByValue($value)
            : $this->types->getByType($type);

        $local = $this->createLocalContext(Direction::Normalize, $context);

        return $concreteType->cast($value, $local);
    }

    /**
     * @throws TypeNotFoundException
     */
    public function isNormalizable(mixed $value, ?string $type = null, ?Context $context = null): bool
    {
        $concreteType = $type === null
            ? $this->types->getByValue($value)
            : $this->types->getByType($type);

        $local = $this->createLocalContext(Direction::Normalize, $context);

        return $concreteType->match($value, $local);
    }

    /**
     * @throws TypeNotFoundException
     */
    public function denormalize(mixed $value, string $type, ?Context $context = null): mixed
    {
        $concreteType = $this->types->getByType($type);

        $local = $this->createLocalContext(Direction::Denormalize, $context);

        return $concreteType->cast($value, $local);
    }

    /**
     * @throws TypeNotFoundException
     */
    public function isDenormalizable(mixed $value, string $type, ?Context $context = null): bool
    {
        $concreteType = $this->types->getByType($type);

        $local = $this->createLocalContext(Direction::Denormalize, $context);

        return $concreteType->match($value, $local);
    }

    private function createLocalContext(Direction $direction, ?Context $context): LocalContext
    {
        return LocalContext::fromContext(
            direction: $direction,
            types: $this->types,
            context: $this->context->with($context),
        );
    }
}
