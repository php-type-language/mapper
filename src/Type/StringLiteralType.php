<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

/**
 * @template-extends LiteralType<string>
 */
class StringLiteralType extends LiteralType
{
    /**
     * @param TypeInterface<string> $type
     */
    public function __construct(string $value, TypeInterface $type = new StringType())
    {
        parent::__construct($value, $type);
    }
}
