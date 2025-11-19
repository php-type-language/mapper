<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

/**
 * @template-extends LiteralType<float>
 */
class FloatLiteralType extends LiteralType
{
    /**
     * @param TypeInterface<float> $type
     */
    public function __construct(int|float $value, TypeInterface $type)
    {
        parent::__construct((float) $value, $type);
    }
}
