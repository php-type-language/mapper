<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface TypeInterface
{
    /**
     * Returns a representation of the type to be displayed in case of
     * errors or in some other cases.
     *
     * The simplest way to determine the type is like this. This example returns
     * a simple type named 'int':
     * ```php
     * return new TypeLang\Parser\Node\Stmt\NamedTypeNode('int');
     * ```
     *
     * To visualize the type, you can use the
     * {@see \TypeLang\Printer\PrinterInterface} object:
     * ```php
     * $printer = new TypeLang\Printer\PrettyPrinter();
     *
     * echo $printer->print(new NamedTypeNode('int'));
     * ```
     */
    public function getTypeStatement(LocalContext $context): TypeStatement;

    public function cast(mixed $value, RegistryInterface $types, LocalContext $context): mixed;
}
