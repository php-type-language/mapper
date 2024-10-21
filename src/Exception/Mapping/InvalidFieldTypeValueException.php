<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Runtime\ContextInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class InvalidFieldTypeValueException extends RuntimeException implements
    ValueExceptionInterface,
    ObjectFieldExceptionInterface,
    MappingExceptionInterface
{
    use ValueProvider;
    use ObjectFieldProvider;
    use TypeProvider;

    /**
     * @var int
     */
    public const CODE_ERROR_INVALID_VALUE = 0x01 + parent::CODE_ERROR_LAST;

    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = self::CODE_ERROR_INVALID_VALUE;

    /**
     * @param non-empty-string $field
     */
    public function __construct(
        protected readonly string $field,
        protected readonly mixed $value,
        protected readonly TypeStatement $expected,
        protected readonly TypeStatement $object,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            path: $path,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * @param non-empty-string $field
     */
    public static function createFromPath(
        string $field,
        mixed $value,
        TypeStatement $expected,
        TypeStatement $object,
        PathInterface $path,
        ?\Throwable $previous = null
    ): self {
        $template = 'Passed value in {{field}} of {{object}} must be of type {{expected}}, but {{value}} given';

        return new self(
            field: $field,
            value: $value,
            expected: $expected,
            object: $object,
            path: $path,
            template: $template,
            code: self::CODE_ERROR_INVALID_VALUE,
            previous: $previous,
        );
    }

    /**
     * @param non-empty-string $field
     */
    public static function createFromContext(
        string $field,
        mixed $value,
        TypeStatement $expected,
        TypeStatement $object,
        ContextInterface $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            field: $field,
            value: $value,
            expected: $expected,
            object: $object,
            path: clone $context->getPath(),
            previous: $previous,
        );
    }
}
