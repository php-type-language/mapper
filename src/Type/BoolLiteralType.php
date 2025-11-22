<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

/**
 * @template-extends LiteralType<bool>
 */
class BoolLiteralType extends LiteralType
{
    /**
     * @param TypeInterface<bool> $type
     */
    public function __construct(bool $value, TypeInterface $type = new BoolType())
    {
        parent::__construct($value, $type);
    }
}
