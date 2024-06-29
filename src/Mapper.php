<?php

declare(strict_types=1);

namespace Serafim\Mapper;

use Serafim\Mapper\Context\LocalContext;
use Serafim\Mapper\Registry\Registry;
use Serafim\Mapper\Registry\RegistryInterface;

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

    public function normalize(mixed $value, ?string $type = null, ?Context $context = null): mixed
    {
        $concreteType = $this->types->get(
            type: $this->types->parse($type ?? \get_debug_type($value)),
        );

        $context = $this->createLocalContext($context);

        return $concreteType->normalize($value, $this->types, $context);
    }

    public function denormalize(mixed $value, string $type, ?Context $context = null): mixed
    {
        $concreteType = $this->types->get(
            type: $this->types->parse($type),
        );

        $context = $this->createLocalContext($context);

        return $concreteType->denormalize($value, $this->types, $context);
    }
}
