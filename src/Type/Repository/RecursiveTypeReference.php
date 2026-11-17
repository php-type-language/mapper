<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * Reference to a type being built, returned by the repository for a
 * recursive type. The repository gives it the type once the type is built.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Type\Repository
 *
 * @template-implements TypeInterface<mixed>
 */
final class RecursiveTypeReference implements TypeInterface
{
    /**
     * @var TypeInterface<mixed>|null
     */
    private ?TypeInterface $type = null;

    /**
     * @param TypeInterface<mixed> $type
     */
    public function resolve(TypeInterface $type): void
    {
        $this->type = $type;
    }

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        return $this->getType()->isDenormalizable($value, $context);
    }

    public function denormalize(mixed $value, Context $context): mixed
    {
        return $this->getType()->denormalize($value, $context);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return $this->getType()->isNormalizable($value, $context);
    }

    public function normalize(mixed $value, Context $context): mixed
    {
        return $this->getType()->normalize($value, $context);
    }

    /**
     * @return TypeInterface<mixed>
     * @throws TypeDefinitionException in case of the referred type is not
     *         built yet
     */
    private function getType(): TypeInterface
    {
        return $this->type ?? throw TypeDefinitionException::becauseRecursiveTypeIsNotBuilt();
    }
}
