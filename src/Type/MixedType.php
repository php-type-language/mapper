<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

final class MixedType implements LogicalTypeInterface
{
    /**
     * @throws TypeNotFoundException
     */
    private function getType(mixed $value, RegistryInterface $types): TypeInterface
    {
        /**
         * @phpstan-ignore-next-line : False-positive, the 'get_debug_type' method returns a non-empty string
         */
        return $types->get(new NamedTypeNode(\get_debug_type($value)));
    }

    public function supportsCasting(mixed $value, LocalContext $context): bool
    {
        return true;
    }

    /**
     * @throws TypeNotFoundException
     */
    public function cast(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        $type = $this->getType($value, $types);

        return $type->cast($value, $types, $context);
    }
}
