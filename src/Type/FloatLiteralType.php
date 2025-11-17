<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

/**
 * @template-extends LiteralType<float>
 */
class FloatLiteralType extends LiteralType
{
    public function __construct(int|float $value)
    {
        parent::__construct((float) $value);
    }
}
