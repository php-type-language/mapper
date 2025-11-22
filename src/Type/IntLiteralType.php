<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

/**
 * @template-extends LiteralType<int>
 */
class IntLiteralType extends LiteralType
{
    /**
     * @param TypeInterface<int> $type
     */
    public function __construct(int $value, TypeInterface $type = new IntType())
    {
        parent::__construct($value, $type);
    }
}
