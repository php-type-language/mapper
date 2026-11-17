<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ShapeType;

use TypeLang\Mapper\Type\TypeInterface;

/**
 * Field of a shape with the type of its value.
 */
final class Field
{
    public function __construct(
        /**
         * @var int|non-empty-string
         */
        public readonly int|string $key,
        /**
         * @var TypeInterface<mixed>
         */
        public readonly TypeInterface $type,
        /**
         * Whether the field may be absent: `a?: int`
         */
        public readonly bool $isOptional = false,
    ) {}
}
