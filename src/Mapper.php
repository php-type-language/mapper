<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use TypeLang\Mapper\Exception\TypeNotCreatableException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Type\Context\Context;
use TypeLang\Mapper\Type\Context\Direction;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Mapper\Type\Repository\Repository;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;

final class Mapper implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private readonly RepositoryInterface $types = new Repository(),
        private readonly Context $context = new Context(),
    ) {}

    /**
     * Returns new mapper instance with new context.
     *
     * @api
     */
    public function withContext(Context $context): self
    {
        return new self($this->types, $context);
    }

    /**
     * Returns new mapper instance with extended context.
     *
     * @api
     */
    public function withAddedContext(Context $context): self
    {
        return $this->withContext($this->context->merge($context));
    }

    /**
     * Returns current mapper platform.
     *
     * @api
     */
    public function getPlatform(): PlatformInterface
    {
        return $this->types->getPlatform();
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
     * @throws TypeNotCreatableException
     * @throws TypeNotFoundException
     */
    public function normalize(mixed $value, ?string $type = null, ?Context $context = null): mixed
    {
        $concreteType = $type === null
            ? $this->types->getByValue($value)
            : $this->types->getByType($type);

        $context = LocalContext::fromContext(
            direction: Direction::Normalize,
            context: $this->context->merge($context),
        );

        return $concreteType->cast($value, $this->types, $context);
    }

    /**
     * @throws TypeNotCreatableException
     * @throws TypeNotFoundException
     */
    public function denormalize(mixed $value, string $type, ?Context $context = null): mixed
    {
        $concreteType = $this->types->getByType($type);

        $context = LocalContext::fromContext(
            direction: Direction::Denormalize,
            context: $this->context->merge($context),
        );

        return $concreteType->cast($value, $this->types, $context);
    }
}
