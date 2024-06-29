<?php

declare(strict_types=1);

namespace Serafim\Mapper\Type\Builder;

use Serafim\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class ObjectNamedTypeBuilder extends NamedTypeBuilder
{
    /**
     * @param class-string $name
     * @param class-string<TypeInterface> $class
     */
    public function __construct(string $name, string $class)
    {
        parent::__construct($name, $class);
    }

    /**
     * Returns {@see true} in case of passed statement contain a subtype's name
     * of {@see ObjectNamedTypeBuilder::$name} argument or false instead.
     *
     * ```
     * $builder = new ObjectNamedTypeBuilder(\DateTimeInterface::class, TypeReference::class);
     *
     * $builder->isSupported(new NamedTypeNode('example'));                 // bool(false)
     * $builder->isSupported(new NamedTypeNode(\DateTimeInterface::class)); // bool(true)
     * $builder->isSupported(new NamedTypeNode(\DateTime::class));          // bool(true)
     * ```
     */
    public function isSupported(TypeStatement $statement): bool
    {
        if (!$statement instanceof NamedTypeNode) {
            return false;
        }

        $actual = $statement->name->toString();

        return \is_a($actual, $this->name, true);
    }
}
