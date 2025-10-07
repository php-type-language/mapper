<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition;

use TypeLang\Parser\Node\Stmt\TypeStatement;

class PropertyTypeNotFoundException extends TypeNotFoundException
{
    public function __construct(
        /**
         * @var class-string
         */
        public readonly string $class,
        /**
         * @var non-empty-string
         */
        public readonly string $property,
        TypeStatement $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($type, $template, $code, $previous);
    }

    /**
     * @param class-string $class
     * @param non-empty-string $property
     */
    public static function becauseTypeOfPropertyNotDefined(
        string $class,
        string $property,
        TypeStatement $type,
        ?\Throwable $previous = null,
    ): self {
        return new self(
            class: $class,
            property: $property,
            type: $type,
            template: \vsprintf('Type "{{type}}" for property %s::$%s is not defined', [
                $class,
                $property,
            ]),
            previous: $previous,
        );
    }
}
