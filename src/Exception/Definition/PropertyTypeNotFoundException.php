<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition;

use TypeLang\Parser\Node\Stmt\TypeStatement;

class PropertyTypeNotFoundException extends TypeNotFoundException
{
    /**
     * @var int
     */
    public const CODE_ERROR_PROPERTY_TYPE_NOT_DEFINED = 0x01 + parent::CODE_ERROR_LAST;

    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = self::CODE_ERROR_PROPERTY_TYPE_NOT_DEFINED;

    /**
     * @param class-string $class
     * @param non-empty-string $property
     */
    public function __construct(
        private readonly string $class,
        private readonly string $property,
        TypeStatement $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($type, $template, $code, $previous);
    }

    /**
     * @api
     * @return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @api
     * @return non-empty-string
     */
    public function getProperty(): string
    {
        return $this->property;
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
            template: 'Type "{{type}}" for property {{class}}::${{property}} is not registered',
            code: self::CODE_ERROR_PROPERTY_TYPE_NOT_DEFINED,
            previous: $previous,
        );
    }
}
