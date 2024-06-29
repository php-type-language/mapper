<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\TypeNotCreatableException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * @template-implements TypeInterface<mixed, mixed>
 */
final class MixedType implements TypeInterface
{
    /**
     * @return TypeInterface<mixed, mixed>
     * @throws TypeNotFoundException
     */
    private function getType(mixed $value, RegistryInterface $types): TypeInterface
    {
        /**
         * @var TypeInterface<mixed, mixed>
         * @phpstan-ignore-next-line : False-positive, the 'get_debug_type' method returns a non-empty string
         */
        return $types->get(new NamedTypeNode(\get_debug_type($value)));
    }

    /**
     * @throws TypeNotFoundException
     */
    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        $type = $this->getType($value, $types);

        return $type->normalize($value, $types, $context);
    }

    /**
     * @throws TypeNotFoundException
     */
    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        $type = $this->getType($value, $types);

        return $type->denormalize($value, $types, $context);
    }
}
