<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\PathInterface;

class MissingFieldTypeException extends RuntimeException implements
    FieldExceptionInterface
{
    use FieldProvider;

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
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        return new self(
            field: $field,
            path: $path,
            template: 'Field {{field}} requires type definition',
            code: self::CODE_ERROR_INVALID_VALUE,
            previous: $previous,
        );
    }

    /**
     * @param non-empty-string $field
     */
    public static function createFromContext(
        string $field,
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            field: $field,
            path: clone $context->getPath(),
            previous: $previous,
        );
    }
}
