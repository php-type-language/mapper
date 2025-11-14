<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Extension\PHPStan\AttributeSyntaxCheckRule;

use PHPStan\Type\ObjectType as PHPStanObjectType;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Extension\PHPStan
 */
final class AttributeTarget
{
    public readonly PHPStanObjectType $type;

    public function __construct(
        /**
         * @var class-string
         */
        public readonly string $attribute,
        /**
         * @var int<0, max>
         */
        public readonly int $argument,
    ) {
        $this->type = new PHPStanObjectType($this->attribute);
    }
}
