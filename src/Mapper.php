<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\TypeNotCreatableException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Registry\Registry;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

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
        return $this->withContext($this->context->merge($context));
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

    /**
     * @param non-empty-string|null $type
     *
     * @throws TypeNotCreatableException
     * @throws TypeNotFoundException
     */
    private function getType(mixed $value, ?string $type): TypeInterface
    {
        if ($type === null) {
            // @phpstan-ignore-next-line : False-positive, the "get_debug_type" method returns a non-empty string
            return $this->types->get(new NamedTypeNode(\get_debug_type($value)));
        }

        return $this->types->get($this->types->parse($type));
    }

    /**
     * @throws TypeNotCreatableException
     * @throws TypeNotFoundException
     */
    public function normalize(mixed $value, ?string $type = null, ?Context $context = null): mixed
    {
        $concreteType = $this->getType($value, $type);

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
        $concreteType = $this->getType($value, $type);

        $context = LocalContext::fromContext(
            direction: Direction::Denormalize,
            context: $this->context->merge($context),
        );

        return $concreteType->cast($value, $this->types, $context);
    }
}
