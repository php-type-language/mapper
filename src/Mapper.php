<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\TypeNotCreatableException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Registry\Registry;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\TypeInterface;

final class Mapper implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private readonly RegistryInterface $types = new Registry(),
        private readonly Context $context = new Context(),
    ) {}

    /**
     * Returns new mapper instance with new context.
     */
    public function withContext(Context $context): self
    {
        return new self($this->types, $context);
    }

    /**
     * Returns new mapper instance with extended context.
     */
    public function withAddedContext(Context $context): self
    {
        return $this->withContext($this->context->with($context));
    }

    /**
     * Returns current mapper platform.
     */
    public function getPlatform(): PlatformInterface
    {
        return $this->types->getPlatform();
    }

    /**
     * Returns current types registry.
     */
    public function getTypes(): RegistryInterface
    {
        return $this->types;
    }

    private function createLocalContext(?Context $context): LocalContext
    {
        return LocalContext::fromContext(
            context: $this->context->with($context),
        );
    }

    /**
     * @param non-empty-string|null $type
     * @return TypeInterface<mixed, mixed>
     * @throws TypeNotCreatableException
     * @throws TypeNotFoundException
     */
    private function getType(mixed $value, ?string $type): TypeInterface
    {
        // @phpstan-ignore-next-line : False-positive, the "get_debug_type" method returns a non-empty string
        $statement = $this->types->parse($type ?? \get_debug_type($value));

        return $this->types->get($statement);
    }

    /**
     * @throws TypeNotCreatableException
     * @throws TypeNotFoundException
     */
    public function normalize(mixed $value, ?string $type = null, ?Context $context = null): mixed
    {
        $concreteType = $this->getType($value, $type);

        $context = $this->createLocalContext($context);

        return $concreteType->normalize($value, $this->types, $context);
    }

    /**
     * @throws TypeNotCreatableException
     * @throws TypeNotFoundException
     */
    public function denormalize(mixed $value, string $type, ?Context $context = null): mixed
    {
        $concreteType = $this->getType($value, $type);

        $context = $this->createLocalContext($context);

        return $concreteType->denormalize($value, $this->types, $context);
    }
}
